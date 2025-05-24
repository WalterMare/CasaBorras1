<?php
session_start();
require_once 'conexiondb.php';

// 1. Validaciones iniciales
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
$idUsuario = $_SESSION['Usuario_Id'];
$idPreliquidacion = intval($_GET['id']);

// 2. Verificar estado actual y obtener período
$sqlEstado = "SELECT idEstadoPre, periodo FROM preliquidacion WHERE idpreliquidacion = ?";
$stmt = $conn->prepare($sqlEstado);
$stmt->bind_param("i", $idPreliquidacion);
$stmt->execute();
$result = $stmt->get_result();
$preliData = $result->fetch_assoc();

if (!$preliData || $preliData['idEstadoPre'] != 1) {
    $_SESSION['error'] = "Solo preliquidaciones PENDIENTES pueden generarse";
    header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
    exit;
}

// 3. Extraer rango de fechas del período
$periodoParts = explode(" a ", $preliData['periodo']);
$primerDiaMesAnterior = $periodoParts[0];
$ultimoDiaMesAnterior = $periodoParts[1];

// 4. Iniciar transacción
$conn->begin_transaction();
$todoCorrecto = true;

try {
    // 5. Procesar cada empleado activo
    $sqlEmpleados = "SELECT idempleado, nombre, apellido FROM empleado WHERE estado = 1";
    $resultEmpleados = $conn->query($sqlEmpleados);

    while ($empleado = $resultEmpleados->fetch_assoc()) {
        $idEmpleado = $empleado['idempleado'];

        $sqlAsistencias = "SELECT COUNT(*) AS diasTrabajados
                   FROM asistencia a
                   JOIN estadoasistencia ea ON a.idEstado = ea.idEstado
                   WHERE a.idEmpleado = ? 
                     AND a.fecha BETWEEN ? AND ? 
                     AND ea.nombreEstado = 'Presente'";
        $stmt = $conn->prepare($sqlAsistencias);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $diasTrabajados = $stmt->get_result()->fetch_assoc()['diasTrabajados'] ?? 0;

        // 5.2 Calcular horas extras
        $sqlHorasExtras = "SELECT SUM(cantidadHoras) AS totalHoras FROM horaextra
                          WHERE IdEmpleado = ? AND fecha BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlHorasExtras);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $totalHorasExtras = $stmt->get_result()->fetch_assoc()['totalHoras'] ?? 0;

        // 5.3 Calcular sanciones
        $sqlSanciones = "SELECT CAST(s.cantidadDias AS UNSIGNED) AS cantidadDias, ts.nombreTipo AS tipoSancion 
                        FROM sancion s JOIN tiposancion ts ON s.IdTipoSancion = ts.idtipoSancion
                        WHERE s.idEmpleado = ? AND s.fecha_inicio BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlSanciones);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $resultSanciones = $stmt->get_result();

        $totalSanciones = 0;
        $tiposSanciones = [];
        while ($sancion = $resultSanciones->fetch_assoc()) {
            $totalSanciones += (int)$sancion['cantidadDias'];
            $tiposSanciones[] = $sancion['tipoSancion'];
        }
        if (empty($tiposSanciones)) {
            $tiposSanciones[] = "No Registra";
        }

        // 5.4 Calcular embargos
        $sqlEmbargos = "SELECT SUM(monto) AS totalEmbargos FROM embargo
                       WHERE idEmpleado = ? AND fecha BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlEmbargos);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $totalEmbargos = $stmt->get_result()->fetch_assoc()['totalEmbargos'] ?? 0;

        // 5.5 Calcular viáticos
        $sqlViaticos = "SELECT v.monto, tv.descripcion AS tipoViatico 
                       FROM viatico v JOIN tipo_viatico tv ON v.idTipo = tv.idTipo_viatico
                       WHERE v.idEmpleado = ? AND v.fechaotorgamiento BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlViaticos);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $resultViaticos = $stmt->get_result();

        $totalViaticos = 0;
        $tiposViaticos = [];
        while ($viatico = $resultViaticos->fetch_assoc()) {
            $totalViaticos += $viatico['monto'];
            $tiposViaticos[] = $viatico['tipoViatico'];
        }

        // 5.6 Calcular licencias
        $sqlLicencias = "SELECT l.cantidaddias, tl.descripcion AS tipoLicencia 
                        FROM licencia l JOIN tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
                        WHERE l.idEmpleado = ? AND l.fechainicio BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlLicencias);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $resultLicencias = $stmt->get_result();

        $totalDiasLicencia = 0;
        $tiposLicencias = [];
        while ($licencia = $resultLicencias->fetch_assoc()) {
            $totalDiasLicencia += $licencia['cantidaddias'];
            $tiposLicencias[] = $licencia['tipoLicencia'];
        }

        // 5.7 Calcular vacaciones
        $sqlVacaciones = "SELECT fecha_inicio, fecha_fin, cantidad_dias 
                         FROM vacaciones 
                         WHERE idempleado = ?
                         AND ((fecha_inicio BETWEEN ? AND ?) OR
                             (fecha_fin BETWEEN ? AND ?) OR
                             (fecha_inicio <= ? AND fecha_fin >= ?))";
        $stmt = $conn->prepare($sqlVacaciones);
        $stmt->bind_param(
            "issssss",
            $idEmpleado,
            $primerDiaMesAnterior,
            $ultimoDiaMesAnterior,
            $primerDiaMesAnterior,
            $ultimoDiaMesAnterior,
            $primerDiaMesAnterior,
            $ultimoDiaMesAnterior
        );
        $stmt->execute();
        $resultVacaciones = $stmt->get_result();

        $totalDiasVacaciones = 0;
        while ($vacacion = $resultVacaciones->fetch_assoc()) {
            $inicio = new DateTime($vacacion['fecha_inicio']);
            $fin = new DateTime($vacacion['fecha_fin']);
            $inicioPeriodo = new DateTime($primerDiaMesAnterior);
            $finPeriodo = new DateTime($ultimoDiaMesAnterior);

            $inicioReal = $inicio > $inicioPeriodo ? $inicio : $inicioPeriodo;
            $finReal = $fin < $finPeriodo ? $fin : $finPeriodo;

            if ($inicioReal <= $finReal) {
                $intervalo = $inicioReal->diff($finReal);
                $diasEnPeriodo = $intervalo->days + 1;
                $totalDiasVacaciones += $diasEnPeriodo;
            }
        }

        // 5.8 Calcular anticipos
        $sqlAnticipos = "SELECT SUM(monto) AS totalAnticipos FROM anticipo
                        WHERE IdEmpleado = ? AND fechaOtorgamiento BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlAnticipos);
        $stmt->bind_param("iss", $idEmpleado, $primerDiaMesAnterior, $ultimoDiaMesAnterior);
        $stmt->execute();
        $totalAnticipos = $stmt->get_result()->fetch_assoc()['totalAnticipos'] ?? 0;

        // 5.9 Obtener obra social
        $sqlObraSocial = "SELECT descripcion FROM obrasocial WHERE idEmpleado = ?";
        $stmt = $conn->prepare($sqlObraSocial);
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $descripcionObraSocial = $stmt->get_result()->fetch_assoc()['descripcion'] ?? 'N/A';

        // 5.10 Obtener familiares
        $sqlFamiliares = "SELECT nombre, apellido FROM familiar WHERE idEmpleado = ?";
        $stmt = $conn->prepare($sqlFamiliares);
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resultFamiliares = $stmt->get_result();

        $familiares = [];
        while ($familiar = $resultFamiliares->fetch_assoc()) {
            $familiares[] = $familiar['nombre'] . ' ' . $familiar['apellido'];
        }
        $familiaresString = empty($familiares) ? 'N/A' : implode(', ', $familiares);

        // 5.11 Insertar detalle (usando consultas preparadas)
        $sqlDetalle = "INSERT INTO detallepreliquidacion
                      (idPreliquidacion, idEmpleado, idHorasExtras, idLicencia, idAnticipo, 
                       idObraSocial, idFamiliar, idSancion, idEmbargo, idViatico, 
                       tiposSanciones, tiposLicencias, diasTrabajados, vacacionesTomadas)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sqlDetalle);
        $stmt->bind_param(
            "iiiiisssiisssi",
            $idPreliquidacion,
            $idEmpleado,
            $totalHorasExtras,
            $totalDiasLicencia,
            $totalAnticipos,
            $descripcionObraSocial,
            $familiaresString,
            $totalSanciones,
            $totalEmbargos,
            $totalViaticos,
            implode(", ", $tiposSanciones),
            implode(", ", $tiposLicencias),
            $diasTrabajados,
            $totalDiasVacaciones
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar detalle para empleado $idEmpleado");
        }
    }

    // 6. Actualizar estado a GENERADA (ID=2)
    $sqlUpdate = "UPDATE preliquidacion SET idEstadoPre = 2 WHERE idpreliquidacion = ?";
    $stmt = $conn->prepare($sqlUpdate);
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
