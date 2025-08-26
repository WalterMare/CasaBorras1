<?php
session_start();
require_once 'conexiondb.php';

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No se especificó preliquidación";
    header('Location: Preliquidacion.php');
    exit;
}

$conn = ConexionBD();
$idPreliquidacion = intval($_GET['id']);

// 1. Verificar estado y período
$sqlEstado = "SELECT idEstadoPre, periodo FROM preliquidacion WHERE idpreliquidacion = ?";
$stmt = $conn->prepare($sqlEstado);
$stmt->bind_param("i", $idPreliquidacion);
$stmt->execute();
$preliData = $stmt->get_result()->fetch_assoc();

if (!$preliData || $preliData['idEstadoPre'] != 1) {
    $_SESSION['error'] = "Solo preliquidaciones PENDIENTES pueden generarse";
    header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
    exit;
}

$periodoParts = explode(" a ", $preliData['periodo']);
$primerDia = $periodoParts[0];
$ultimoDia = $periodoParts[1];

$conn->begin_transaction();

try {
    // 2. Procesar empleados activos
    $sqlEmpleados = "SELECT idempleado, nombre, apellido FROM empleado WHERE estado = 1";
    $resultEmpleados = $conn->query($sqlEmpleados);

    while ($empleado = $resultEmpleados->fetch_assoc()) {
        $idEmpleado = $empleado['idempleado'];

        // 2.1 Calcular totales
        // Días trabajados
        $stmt = $conn->prepare("SELECT COUNT(*) AS diasTrabajados
                                FROM asistencia a
                                JOIN estadoasistencia ea ON a.idEstado = ea.idEstado
                                WHERE a.idEmpleado=? AND a.fecha BETWEEN ? AND ? AND ea.nombreEstado='Presente'");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $diasTrabajados = $stmt->get_result()->fetch_assoc()['diasTrabajados'] ?? 0;

        // Horas extras
        $stmt = $conn->prepare("SELECT SUM(cantidadHoras) AS totalHoras, SUM(valorHoraExtra*cantidadHoras) AS totalValor 
                                FROM horaextra WHERE IdEmpleado=? AND fecha BETWEEN ? AND ?");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $heData = $stmt->get_result()->fetch_assoc();
        $totalHorasExtras = $heData['totalHoras'] ?? 0;
        $totalValorHoras = $heData['totalValor'] ?? 0;

        // Licencias
        $stmt = $conn->prepare("SELECT SUM(cantidaddias) AS totalDiasLicencia 
                                FROM licencia WHERE idEmpleado=? AND fechainicio BETWEEN ? AND ?");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $totalDiasLicencia = $stmt->get_result()->fetch_assoc()['totalDiasLicencia'] ?? 0;

        // Sanciones
        $stmt = $conn->prepare("SELECT SUM(CAST(cantidadDias AS UNSIGNED)) AS totalSanciones 
                                FROM sancion WHERE idEmpleado=? AND fecha_inicio BETWEEN ? AND ?");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $totalSanciones = $stmt->get_result()->fetch_assoc()['totalSanciones'] ?? 0;

        // Embargos
        $stmt = $conn->prepare("SELECT SUM(monto) AS totalEmbargos FROM embargo WHERE idEmpleado=? AND fecha BETWEEN ? AND ?");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $totalEmbargos = $stmt->get_result()->fetch_assoc()['totalEmbargos'] ?? 0;

        // Viáticos
        $stmt = $conn->prepare("SELECT SUM(monto) AS totalViaticos FROM viatico WHERE idEmpleado=? AND fechaotorgamiento BETWEEN ? AND ?");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $totalViaticos = $stmt->get_result()->fetch_assoc()['totalViaticos'] ?? 0;

        // Anticipos
        $stmt = $conn->prepare("SELECT SUM(monto) AS totalAnticipos FROM anticipo WHERE IdEmpleado=? AND fechaOtorgamiento BETWEEN ? AND ?");
        $stmt->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmt->execute();
        $totalAnticipos = $stmt->get_result()->fetch_assoc()['totalAnticipos'] ?? 0;

        // Obra social
        $stmt = $conn->prepare("SELECT descripcion FROM obrasocial WHERE idEmpleado=?");
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $obraSocial = $stmt->get_result()->fetch_assoc()['descripcion'] ?? 'N/A';

        // Familiares
        $stmt = $conn->prepare("SELECT nombre, apellido FROM familiar WHERE idEmpleado=?");
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resFam = $stmt->get_result();
        $familiares = [];
        while ($fam = $resFam->fetch_assoc()) $familiares[] = $fam['nombre']." ".$fam['apellido'];
        $familiaresStr = empty($familiares) ? 'N/A' : implode(', ', $familiares);

        // Vacaciones (días)
        $stmt = $conn->prepare("SELECT fecha_inicio, fecha_fin FROM vacaciones WHERE idempleado=?");
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resVac = $stmt->get_result();
        $totalDiasVacaciones = 0;
        while ($vac = $resVac->fetch_assoc()) {
            $inicio = new DateTime($vac['fecha_inicio']);
            $fin = new DateTime($vac['fecha_fin']);
            $inicioPeriodo = new DateTime($primerDia);
            $finPeriodo = new DateTime($ultimoDia);
            $inicioReal = $inicio > $inicioPeriodo ? $inicio : $inicioPeriodo;
            $finReal = $fin < $finPeriodo ? $fin : $finPeriodo;
            if ($inicioReal <= $finReal) {
                $interval = $inicioReal->diff($finReal);
                $totalDiasVacaciones += $interval->days + 1;
            }
        }

        // 2.2 Insertar totales en detallepreliquidacion
        $stmt = $conn->prepare("INSERT INTO detallepreliquidacion
            (idPreliquidacion, idEmpleado, idHorasExtras, idLicencia, idAnticipo, idObraSocial, idFamiliar, idSancion, idEmbargo, idViatico, diasTrabajados, vacacionesTomadas)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiissiiidd",
            $idPreliquidacion, $idEmpleado, $totalHorasExtras, $totalDiasLicencia, $totalAnticipos,
            $obraSocial, $familiaresStr, $totalSanciones, $totalEmbargos, $totalViaticos, $diasTrabajados, $totalDiasVacaciones
        );
        $stmt->execute();
        $idDetalle = $conn->insert_id;

        // 3️⃣ Insertar registros individuales en tablas detalle

        // Horas extras
        $stmtHE = $conn->prepare("SELECT fecha, tipoHora, cantidadHoras, tipoRecargo, valorHoraExtra
                                  FROM horaextra WHERE IdEmpleado=? AND fecha BETWEEN ? AND ?");
        $stmtHE->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmtHE->execute();
        
        $resHE = $stmtHE->get_result();
        while ($he = $resHE->fetch_assoc()) {
            $totalHE = $he['cantidadHoras'] * $he['valorHoraExtra'];
            $tipoRecargo = ($he['tipoRecargo'] == '50' || $he['tipoRecargo'] == 50) ? '50%' : '100%';
            $stmtInsHE = $conn->prepare("INSERT INTO detalle_horasextras_preliquidacion
                (idDetallePreliquidacion, fecha, tipoHora, cantidad, tipoRecargo, valorHoraExtra, total)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsHE->bind_param("issdsdd", $idDetalle, $he['fecha'], $he['tipoHora'], $he['cantidadHoras'], $he['tipoRecargo'], $he['valorHoraExtra'], $totalHE);
            $stmtInsHE->execute();
        }

        // Licencias
        $stmtLic = $conn->prepare("SELECT l.fechainicio, l.fechafin, l.cantidaddias, tl.descripcion AS tipoLicencia, el.nombreEstado
                                   FROM licencia l
                                   JOIN tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
                                   JOIN estadolicencia el ON l.IdEstado = el.idestadoLicencia
                                   WHERE l.idEmpleado=? AND l.fechainicio BETWEEN ? AND ?");
        $stmtLic->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmtLic->execute();
        $resLic = $stmtLic->get_result();
        while ($lic = $resLic->fetch_assoc()) {
            $stmtInsLic = $conn->prepare("INSERT INTO detalle_licencias_preliquidacion
                (idDetallePreliquidacion, tipoLicencia, fechaInicio, fechaFin, dias, estado)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsLic->bind_param("isssss", $idDetalle, $lic['tipoLicencia'], $lic['fechainicio'], $lic['fechafin'], $lic['cantidaddias'], $lic['nombreEstado']);
            $stmtInsLic->execute();
        }

        // Viáticos
        $stmtV = $conn->prepare("SELECT v.fechaotorgamiento, tv.descripcion AS tipoViatico, v.monto
                                 FROM viatico v JOIN tipo_viatico tv ON v.idTipo = tv.idTipo_viatico
                                 WHERE v.idEmpleado=? AND v.fechaotorgamiento BETWEEN ? AND ?");
        $stmtV->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmtV->execute();
        $resV = $stmtV->get_result();
        while ($vi = $resV->fetch_assoc()) {
            $stmtInsV = $conn->prepare("INSERT INTO detalle_viaticos_preliquidacion
                (idDetallePreliquidacion, fecha, tipoViatico, monto)
                VALUES (?, ?, ?, ?)");
            $stmtInsV->bind_param("issd", $idDetalle, $vi['fechaotorgamiento'], $vi['tipoViatico'], $vi['monto']);
            $stmtInsV->execute();
        }

        // Sanciones
        $stmtS = $conn->prepare("SELECT s.fecha_inicio, s.fecha_fin, CAST(s.cantidadDias AS UNSIGNED) AS dias, ts.nombreTipo AS tipoSancion, es.nombres AS estado
                                 FROM sancion s
                                 JOIN tiposancion ts ON s.IdTipoSancion = ts.idtipoSancion
                                 JOIN estadosancion es ON s.idEstadoSancion = es.idestadoSancion
                                 WHERE s.idEmpleado=? AND s.fecha_inicio BETWEEN ? AND ?");
        $stmtS->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmtS->execute();
        $resS = $stmtS->get_result();
        while ($san = $resS->fetch_assoc()) {
            $stmtInsS = $conn->prepare("INSERT INTO detalle_sanciones_preliquidacion
                (idDetallePreliquidacion, tipoSancion, fechaInicio, fechaFin, dias, estado)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsS->bind_param("isssis", $idDetalle, $san['tipoSancion'], $san['fecha_inicio'], $san['fecha_fin'], $san['dias'], $san['estado']);
            $stmtInsS->execute();
        }

        // Embargos
        $stmtE = $conn->prepare("SELECT expediente, tipo, fecha, fecha_inicio, fecha_fin, monto, porcentaje, estado, descripcion
                                 FROM embargo
                                 WHERE idEmpleado=? AND fecha BETWEEN ? AND ?");
        $stmtE->bind_param("iss", $idEmpleado, $primerDia, $ultimoDia);
        $stmtE->execute();
        $resE = $stmtE->get_result();
        while ($emb = $resE->fetch_assoc()) {
            $stmtInsE = $conn->prepare("INSERT INTO detalle_embargos_preliquidacion
                (idDetallePreliquidacion, expediente, tipo, fecha, fechaInicio, fechaFin, monto, porcentaje, estado, descripcion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsE->bind_param("issssddiss", $idDetalle, $emb['expediente'], $emb['tipo'], $emb['fecha'], $emb['fecha_inicio'], $emb['fecha_fin'], $emb['monto'], $emb['porcentaje'], $emb['estado'], $emb['descripcion']);
            $stmtInsE->execute();
        }

    } // fin foreach empleado

    // 4. Actualizar preliquidación a GENERADA
    $stmt = $conn->prepare("UPDATE preliquidacion SET idEstadoPre=2 WHERE idpreliquidacion=?");
    $stmt->bind_param("i", $idPreliquidacion);
    $stmt->execute();

    $conn->commit();
    $_SESSION['mensaje'] = "¡Preliquidación generada con éxito!";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error al generar: " . $e->getMessage();
}

header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
$conn->close();
?>