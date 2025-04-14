
<?php
session_start();
require_once 'conexiondb.php';

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

function generarPreliquidacion($conn, $idUsuario)
{
    $todoCorrecto = true; // Variable de control

    // Obtener el primer y último día del mes anterior
    $primerDiaMesAnterior = date('Y-m-01', strtotime('last month'));
    $ultimoDiaMesAnterior = date('Y-m-t', strtotime('last month'));

    // Obtener la fecha actual
    $fechaEjecucion = date('Y-m-d H:i:s');
    $periodo = "$primerDiaMesAnterior a $ultimoDiaMesAnterior";

    // Insertar la preliquidación
    $sqlPreliquidacion = "INSERT INTO preliquidacion (fecha, periodo, idEstadoPre, idUsuario) 
                          VALUES ('$fechaEjecucion', '$periodo', 2, $idUsuario)";

    if ($conn->query($sqlPreliquidacion) === TRUE) {
        $idPreliquidacion = $conn->insert_id; // Obtener el ID de la preliquidación

        // Obtener todos los empleados activos
        $sqlEmpleados = "SELECT idempleado, nombre, apellido FROM empleado";
        $resultEmpleados = $conn->query($sqlEmpleados);

        while ($empleado = $resultEmpleados->fetch_assoc()) {
            $idEmpleado = $empleado['idempleado'];
            $nombreCompleto = $empleado['nombre'] . ' ' . $empleado['apellido'];

            // Obtener asistencia
            $sqlAsistencias = "SELECT COUNT(*) AS diasTrabajados FROM asistencias
                               WHERE idEmpleado = $idEmpleado
                               AND fecha BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'
                               AND estado = 'Presente'";
            $resultAsistencias = $conn->query($sqlAsistencias);
            $diasTrabajados = $resultAsistencias->fetch_assoc()['diasTrabajados'] ?? 0;

            // Obtener horas extras
            $sqlHorasExtras = "SELECT SUM(cantidadHoras) AS totalHoras FROM horaextra
                               WHERE IdEmpleado = $idEmpleado
                               AND fecha BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'";
            $resultHorasExtras = $conn->query($sqlHorasExtras);
            $totalHorasExtras = $resultHorasExtras->fetch_assoc()['totalHoras'] ?? 0;

            // Obtener sanciones
            $sqlSanciones = "SELECT CAST(s.cantidadDias AS UNSIGNED) AS cantidadDias, ts.nombreTipo AS tipoSancion 
            FROM sancion s
            JOIN tiposancion ts ON s.IdTipoSancion = ts.idtipoSancion
            WHERE s.idEmpleado = $idEmpleado
            AND s.fecha_inicio BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'";
            $resultSanciones = $conn->query($sqlSanciones);

            $totalSanciones = 0;
            $tiposSanciones = [];

            if ($resultSanciones && $resultSanciones->num_rows > 0) {
                while ($sancion = $resultSanciones->fetch_assoc()) {
                    $totalSanciones += (int)$sancion['cantidadDias'];
                    $tiposSanciones[] = $sancion['tipoSancion'];
                }
            } else {
                $totalSanciones = "No Registra";
                $tiposSanciones[] = "No Registra";
            }


            // Obtener embargos
            $sqlEmbargos = "SELECT SUM(monto) AS totalEmbargos FROM embargo
                            WHERE idEmpleado = $idEmpleado
                            AND fecha BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'";
            $resultEmbargos = $conn->query($sqlEmbargos);
            $totalEmbargos = $resultEmbargos->fetch_assoc()['totalEmbargos'] ?? 0;

            // Obtener viáticos
            $sqlViaticos = "SELECT v.monto, tv.descripcion AS tipoViatico FROM viatico v
                            JOIN tipo_viatico tv ON v.idTipo = tv.idTipo_viatico
                            WHERE v.idEmpleado = $idEmpleado
                            AND v.fechaotorgamiento BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'";
            $resultViaticos = $conn->query($sqlViaticos);
            $totalViaticos = 0;
            $tiposViaticos = [];
            while ($viatico = $resultViaticos->fetch_assoc()) {
                $totalViaticos += $viatico['monto'];
                $tiposViaticos[] = $viatico['tipoViatico'];
            }

            // Obtener licencias
            $sqlLicencias = "SELECT l.cantidaddias, tl.descripcion AS tipoLicencia FROM licencia l
                             JOIN tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
                             WHERE l.idEmpleado = $idEmpleado
                             AND l.fechainicio BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'";
            $resultLicencias = $conn->query($sqlLicencias);
            $totalDiasLicencia = 0;
            $tiposLicencias = [];
            while ($licencia = $resultLicencias->fetch_assoc()) {
                $totalDiasLicencia += $licencia['cantidaddias'];
                $tiposLicencias[] = $licencia['tipoLicencia'];
            }

            // Obtener anticipos
            $sqlAnticipos = "SELECT SUM(monto) AS totalAnticipos FROM anticipo
                     WHERE IdEmpleado = $idEmpleado
                     AND fechaOtorgamiento BETWEEN '$primerDiaMesAnterior' AND '$ultimoDiaMesAnterior'";
            $resultAnticipos = $conn->query($sqlAnticipos);
            $totalAnticipos = $resultAnticipos->fetch_assoc()['totalAnticipos'] ?? 0;

            // Obtener obra social
            $sqlObraSocial = "SELECT descripcion FROM obrasocial WHERE idEmpleado = $idEmpleado";
            $resultObraSocial = $conn->query($sqlObraSocial);
            $descripcionObraSocial = $resultObraSocial->fetch_assoc()['descripcion'] ?? 'N/A';

            // Obtener familiares
            $sqlFamiliares = "SELECT nombre, apellido FROM familiar WHERE idEmpleado = $idEmpleado";
            $resultFamiliares = $conn->query($sqlFamiliares);
            $familiares = [];
            while ($familiar = $resultFamiliares->fetch_assoc()) {
                $familiares[] = $familiar['nombre'] . ' ' . $familiar['apellido'];
            }
            $familiaresString = empty($familiares) ? 'N/A' : implode(', ', $familiares);

            // Insertar el detalle de la preliquidación
            $sqlDetallePreliquidacion = "INSERT INTO detallepreliquidacion
                (idPreliquidacion, idEmpleado, idHorasExtras, idLicencia, idAnticipo, idObraSocial, idFamiliar, idSancion, idEmbargo, idViatico, tiposSanciones, tiposLicencias, diasTrabajados)
                VALUES
                ($idPreliquidacion, $idEmpleado, $totalHorasExtras, $totalDiasLicencia, $totalAnticipos, '$descripcionObraSocial', '$familiaresString', $totalSanciones, $totalEmbargos, $totalViaticos, '" . implode(", ", $tiposSanciones) . "', '" . implode(", ", $tiposLicencias) . "', $diasTrabajados)";

            if ($conn->query($sqlDetallePreliquidacion) !== TRUE) {
                $todoCorrecto = false; // Si hay un error, marcar como falso
            }
        }
    } else {
        return false; // Falló la preliquidación principal
    }

    return $todoCorrecto; // Retorna true si todo salió bien, false si hubo errores
}

// Uso de la función
$conn = ConexionBD();
$idUsuario = $_SESSION['Usuario_Id'];

if (generarPreliquidacion($conn, $idUsuario)) {
    echo "Preliquidación generada correctamente.";
} else {
    echo "Error en la generación de la preliquidación.";
}

$conn->close();
?>
