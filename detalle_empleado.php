<?php
session_start();
require_once 'conexiondb.php';
$conexion = ConexionBD();

require_once 'Select_tipoLicencia.php';
require_once 'Select_sancion_preliquidacion.php';
require_once 'select_viatico_preliquidacion.php';

if (!isset($_GET['id']) || !isset($_GET['preliquidacion'])) {
    die("Error: Datos insuficientes.");
}

$idEmpleado = intval($_GET['id']);
$idPreliquidacion = intval($_GET['preliquidacion']);
$diasADescontar = 0;
$totalMonto = 0;
$diasSuspension = 0;

function Obtener_Detalles_Empleado($vConexion, $idEmpleado, $idPreliquidacion)
{
    $consulta = "SELECT 
        e.nombre, e.apellido, e.dni, e.fecha_inicio,
        c.sueldo_basico, c.descripcion AS cargo,
        ec.descripcion AS estado_civil,
            (SELECT COUNT(*) 
             FROM familiar f 
             WHERE f.IdEmpleado = e.idempleado 
               AND f.Idrelacion = 3) AS cantidad_hijos,  
        dp.idLicencia, dp.idAnticipo, 
        dp.idObraSocial, dp.idFamiliar, dp.idSancion, 
        dp.idEmbargo, dp.idViatico,
        dp.diasTrabajados,
        dp.tiposSanciones AS tipoSancion,  
        dp.tiposLicencias AS tipoLicencia,
        dp.vacacionesTomadas AS vacacionesTomadas,
        COALESCE(MAX(v.vacaciones_restantes), 0) AS vacacionesRestantes
    FROM 
        detallepreliquidacion dp
    INNER JOIN 
        empleado e ON dp.idEmpleado = e.idempleado
    LEFT JOIN
        cargo c ON e.idCargo = c.idcargo
    LEFT JOIN
        estadocivil ec ON e.IdestadoCivil = ec.idestadocivil
    LEFT JOIN
        tipolicencia tl ON dp.tiposLicencias = tl.idtipoLicencia
    LEFT JOIN
        vacaciones v ON v.idempleado = e.idempleado AND v.anio = YEAR(CURDATE())  
    WHERE 
        dp.idEmpleado = ? AND dp.idPreliquidacion = ?
    GROUP BY 
        e.idempleado, c.sueldo_basico, c.descripcion, dp.idLicencia, dp.idAnticipo, 
        dp.idObraSocial, dp.idFamiliar, dp.idSancion, dp.idEmbargo, dp.idViatico, 
        dp.diasTrabajados, dp.tiposSanciones, dp.tiposLicencias, dp.vacacionesTomadas";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "ii", $idEmpleado, $idPreliquidacion);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}


function Obtener_Horas_Extras_Empleado($conexion, $idEmpleado, $idPreliquidacion)
{
    $consultaFecha = "SELECT periodo FROM preliquidacion WHERE idPreliquidacion = ?";
    $stmtFecha = mysqli_prepare($conexion, $consultaFecha);
    mysqli_stmt_bind_param($stmtFecha, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtFecha);
    $resultadoFecha = mysqli_stmt_get_result($stmtFecha);
    $filaFecha = mysqli_fetch_assoc($resultadoFecha);

    if (!$filaFecha) {
        return []; // Preliquidación no encontrada
    }

    $periodo = $filaFecha['periodo']; // Ej: "2025-04-01 a 2025-04-30"
    $fechas = explode(' a ', $periodo);
    $fechaInicio = $fechas[0];

    $mes = date('m', strtotime($fechaInicio));
    $anio = date('Y', strtotime($fechaInicio));

    $consulta = "SELECT *
                 FROM horaextra
                 WHERE IdEmpleado = ?
                 AND MONTH(fecha) = ? AND YEAR(fecha) = ?";

    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, "iii", $idEmpleado, $mes, $anio);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $horasExtras = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $horasExtras[] = $fila;
    }

    return $horasExtras;
}

function Obtener_Vacaciones_Empleado_Por_Periodo($vConexion, $idEmpleado, $idPreliquidacion)
{
    // Obtener el periodo de la preliquidacion
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($vConexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $resultadoPeriodo = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($resultadoPeriodo);

    if (!$filaPeriodo) {
        return []; // No se encontró el período
    }

    // Separar las fechas de inicio y fin del período (asumiendo formato 'YYYY-MM-DD a YYYY-MM-DD')
    $partes = explode(' a ', $filaPeriodo['periodo']);
    if (count($partes) != 2) {
        return []; // Formato incorrecto
    }
    $fechaInicioPeriodo = $partes[0];
    $fechaFinPeriodo = $partes[1];

    // Consultar vacaciones que se crucen con el período
    $consultaVacaciones = "SELECT fecha_inicio, fecha_fin, cantidad_dias, estado
                           FROM vacaciones
                           WHERE idempleado = ?
                           AND fecha_inicio <= ?
                           AND fecha_fin >= ?
                           ORDER BY fecha_inicio DESC";

    $stmtVac = mysqli_prepare($vConexion, $consultaVacaciones);
    mysqli_stmt_bind_param($stmtVac, "iss", $idEmpleado, $fechaFinPeriodo, $fechaInicioPeriodo);
    mysqli_stmt_execute($stmtVac);
    $resultadoVac = mysqli_stmt_get_result($stmtVac);

    $vacaciones = [];
    while ($fila = mysqli_fetch_assoc($resultadoVac)) {
        $vacaciones[] = $fila;
    }
    return $vacaciones;
}
function Obtener_Detalles_Jornada_Empleado($vConexion, $idEmpleado, $idPreliquidacion)
{
    // 1. Obtener el periodo desde la tabla preliquidacion
    $queryPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmt = mysqli_prepare($vConexion, $queryPeriodo);
    mysqli_stmt_bind_param($stmt, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);

    if (!$fila || empty($fila['periodo'])) {
        return [
            'periodoTexto' => 'No disponible',
            'diasPeriodo' => 0,
            'diasLaboralesProgramados' => 0,
            'diasTrabajados' => 0,
            'inasistencias' => 0
        ];
    }

    // 2. Parsear el período
    $periodo = explode(" a ", $fila['periodo']);
    if (count($periodo) != 2) {
        return [
            'periodoTexto' => 'Formato inválido',
            'diasPeriodo' => 0,
            'diasLaboralesProgramados' => 0,
            'diasTrabajados' => 0,
            'inasistencias' => 0
        ];
    }

    $fechaInicio = trim($periodo[0]);
    $fechaFin = trim($periodo[1]);

    // 3. Calcular días del periodo
    $fechaInicioDT = new DateTime($fechaInicio);
    $fechaFinDT = new DateTime($fechaFin);
    $intervalo = $fechaInicioDT->diff($fechaFinDT);
    $diasDelPeriodo = $intervalo->days + 1;

    // 4. Contar días laborales programados usando la función creada
    $diasLaboralesProgramados = contarDiasLaboralesProgramados($idEmpleado, $fechaInicio, $fechaFin, $vConexion);

    // 5. Estados que cuentan como trabajados
    $estadosValidos = [1, 3, 4]; // 1=Presente, 3=Justificado, 4=Licencia
    $placeholders = implode(',', array_fill(0, count($estadosValidos), '?'));

    $consultaAsistencias = "
        SELECT COUNT(*) AS diasTrabajados 
        FROM asistencia 
        WHERE idEmpleado = ? 
        AND fecha BETWEEN ? AND ? 
        AND idEstado IN ($placeholders)
    ";

    $stmt2 = mysqli_prepare($vConexion, $consultaAsistencias);

    $tipos = 'iss' . str_repeat('i', count($estadosValidos));
    mysqli_stmt_bind_param(
        $stmt2,
        $tipos,
        $idEmpleado,
        $fechaInicio,
        $fechaFin,
        ...$estadosValidos
    );

    mysqli_stmt_execute($stmt2);
    $resultado2 = mysqli_stmt_get_result($stmt2);
    $fila2 = mysqli_fetch_assoc($resultado2);
    $diasTrabajados = $fila2['diasTrabajados'] ?? 0;

    // 6. Inasistencias = días laborales programados - días trabajados
    $inasistencias = $diasLaboralesProgramados - $diasTrabajados;

    return [
        'periodoTexto' => $fechaInicio . ' a ' . $fechaFin,
        'diasPeriodo' => $diasDelPeriodo,
        'diasLaboralesProgramados' => $diasLaboralesProgramados,
        'diasTrabajados' => $diasTrabajados,
        'inasistencias' => $inasistencias
    ];
}

function Listar_Embargos_Empleado($conexion, $idEmpleado, $idPreliquidacion)
{
    // Obtener el periodo de la preliquidación
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($conexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $resultadoPeriodo = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($resultadoPeriodo);

    if (!$filaPeriodo) {
        return []; // No se encontró el período
    }

    $partes = explode(' a ', $filaPeriodo['periodo']);
    if (count($partes) != 2) {
        return []; // Formato incorrecto
    }
    $fechaInicio = $partes[0];
    $fechaFin = $partes[1];

    // Consultar embargos del empleado que se crucen con el período o que sean indefinidos
    $consultaEmbargos = "SELECT e.idempleado, e.nombre, e.apellido, em.idembargo, em.expediente,
                                em.tipo, em.fecha, em.fecha_inicio, em.fecha_fin, em.monto, em.porcentaje, em.estado,
                                em.descripcion, c.sueldo_basico
                         FROM embargo em
                         INNER JOIN empleado e ON em.idEmpleado = e.idempleado
                         LEFT JOIN cargo c ON e.idCargo = c.idcargo
                         WHERE em.idEmpleado = ?
                         AND (
                             (em.fecha_inicio <= ? AND (em.fecha_fin >= ? OR em.fecha_fin IS NULL))
                             OR (em.fecha_fin IS NULL)
                         )
                         ORDER BY em.fecha_inicio ASC";

    $stmtEmbargos = mysqli_prepare($conexion, $consultaEmbargos);
    mysqli_stmt_bind_param($stmtEmbargos, "iss", $idEmpleado, $fechaFin, $fechaInicio);
    mysqli_stmt_execute($stmtEmbargos);
    $resultadoEmbargos = mysqli_stmt_get_result($stmtEmbargos);

    $embargos = [];
    while ($fila = mysqli_fetch_assoc($resultadoEmbargos)) {
        $embargos[] = $fila;
    }

    return $embargos;
}

function contarDiasLaboralesProgramados($idEmpleado, $fechaInicio, $fechaFin, $conexion)
{
    $diasLaborales = 0;

    // Paso 1: Obtener turnos asignados al empleado para el período
    $sqlTurnos = "SELECT idturno, fecha_asignacion FROM empleado_turno 
                  WHERE idempleado = ? AND fecha_asignacion <= ? ORDER BY fecha_asignacion DESC";
    $stmtTurnos = mysqli_prepare($conexion, $sqlTurnos);
    mysqli_stmt_bind_param($stmtTurnos, "is", $idEmpleado, $fechaFin);
    mysqli_stmt_execute($stmtTurnos);
    $resultadoTurnos = mysqli_stmt_get_result($stmtTurnos);
    $turnos = mysqli_fetch_all($resultadoTurnos, MYSQLI_ASSOC);

    if (empty($turnos)) {
        return 0; // No tiene turno asignado
    }

    // Tomamos el turno vigente más reciente antes o en fechaFin
    $turnoVigente = $turnos[0]['idturno'];

    // Paso 2: Obtener días laborales para ese turno
    $sqlDiasTurno = "SELECT dia_semana FROM turno_dia_horario WHERE idturno = ?";
    $stmtDiasTurno = mysqli_prepare($conexion, $sqlDiasTurno);
    mysqli_stmt_bind_param($stmtDiasTurno, "i", $turnoVigente);
    mysqli_stmt_execute($stmtDiasTurno);
    $resultadoDiasTurno = mysqli_stmt_get_result($stmtDiasTurno);
    $diasTurno = array_map('strtolower', array_column(mysqli_fetch_all($resultadoDiasTurno, MYSQLI_ASSOC), 'dia_semana'));

    // Paso 3: contar días en el período que coinciden con días laborales
    $mapDias = [
        'monday' => 'lunes',
        'tuesday' => 'martes',
        'wednesday' => 'miércoles',
        'thursday' => 'jueves',
        'friday' => 'viernes',
        'saturday' => 'sábado',
        'sunday' => 'domingo',
    ];

    $fechaIter = new DateTime($fechaInicio);
    $fechaFinDT = new DateTime($fechaFin);

    while ($fechaIter <= $fechaFinDT) {
        $nombreDiaEng = strtolower($fechaIter->format('l'));
        $nombreDiaEsp = $mapDias[$nombreDiaEng] ?? '';
        if (in_array($nombreDiaEsp, $diasTurno)) {
            $diasLaborales++;
        }
        $fechaIter->modify('+1 day');
    }

    return $diasLaborales;
}

$infoJornada = Obtener_Detalles_Jornada_Empleado($conexion, $idEmpleado, $idPreliquidacion);
$detalle = Obtener_Detalles_Empleado($conexion, $idEmpleado, $idPreliquidacion);
$horasExtras = Obtener_Horas_Extras_Empleado($conexion, $idEmpleado, $idPreliquidacion);
$licencias = Listar_Licencia_Empleado_preliquidacion($conexion, $idEmpleado, $idPreliquidacion);
$viaticos = Listar_Viaticos_Empleado($conexion, $idEmpleado, $idPreliquidacion);
$sanciones = Listar_Sancion_Empleado_Preliquidacion($conexion, $idEmpleado, $idPreliquidacion);
$vacaciones = Obtener_Vacaciones_Empleado_Por_Periodo($conexion, $idEmpleado, $idPreliquidacion);
$embargos = Listar_Embargos_Empleado($conexion, $idEmpleado, $idPreliquidacion);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Detalles del Empleado</title>

    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">

    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>
    <?php include 'partes/header.php'; ?>
    <?php include 'partes/menu.php'; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1> Preliquidaciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Gestor de Reportes</li>
                    <li class="breadcrumb-item active">Preliquidaciones</li>
                    <li class="breadcrumb-item active">Detalle Preliquidación</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- Encabezado de Detalles del Empleado -->
                            <div class="mb-4 p-3 bg-light border rounded">
                                <h4 class="mb-2">Detalles de <strong><?php echo $detalle['nombre'] . " " . $detalle['apellido']; ?></strong></h4>
                                <p class="mb-1"><strong>DNI:</strong> <?php echo $detalle['dni']; ?></p>
                                <p class="mb-1"><strong>Cargo:</strong><?php echo $detalle['cargo']; ?></p>

                                <p class="mb-1"><strong>Fecha de inicio de actividad:</strong> <?php echo date('d/m/Y', strtotime($detalle['fecha_inicio'])); ?></p>
                                <?php
                                // Calcular antigüedad
                                $inicio = new DateTime($detalle['fecha_inicio']);
                                $hoy = new DateTime();
                                $antiguedad = $inicio->diff($hoy);
                                ?>
                                <p class="mb-1"><strong>Antigüedad:</strong> <?php echo $antiguedad->y . ' años, ' . $antiguedad->m . ' meses'; ?></p>
                                <p class="mb-1"><strong>Estado Civil:</strong> <?php echo $detalle['estado_civil']; ?></p>
                                <p class="mb-1"><strong>Hijos a cargo:</strong> <?php echo !empty($detalle['cantidad_hijos']) ? $detalle['cantidad_hijos'] : 0; ?></p>
                            </div>
                        </div>

                        <!-- Contenido distribuido en tarjetas -->
                        <div class="row g-3">

                            <div class="mb-4">
                                <h5 class="text-dark"><i class="fas fa-calendar-check"></i> Jornada</h5>
                                <table class="table table-bordered table-hover">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Periodo</th>
                                            <th>Días del Periodo</th>
                                            <th>Días laborales programados</th>
                                            <th>Días trabajados</th>
                                            <th>Inasistencias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo htmlspecialchars($infoJornada['periodoTexto']); ?></td>
                                            <td><?php echo intval($infoJornada['diasPeriodo']); ?></td>
                                            <td><?php echo intval($infoJornada['diasLaboralesProgramados']); ?></td>
                                            <td><?php echo intval($infoJornada['diasTrabajados']); ?></td>
                                            <td><?php echo intval($infoJornada['inasistencias']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>





                            <!-- LICENCIAS -->
                            <div class="mb-4">
                                <h5 class="text-warning"><i class="fas fa-file-medical"></i> Licencias activas</h5>
                                <?php if (!empty($licencias)): ?>
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Inicio</th>
                                                <th>Fin</th>
                                                <th>Días</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($licencias as $lic): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($lic['NOMBRETIPO']); ?></td>
                                                    <td><?php echo htmlspecialchars($lic['FECHAINICIO']); ?></td>
                                                    <td><?php echo htmlspecialchars($lic['FECHAFIN']); ?></td>
                                                    <td><?php echo htmlspecialchars($lic['DIAS']); ?></td>
                                                    <td><?php echo htmlspecialchars($lic['ESTADO']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="text-muted">No registra licencias activas.</p>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <h5 class="text-secondary"><i class="fas fa-gavel"></i> Vacaciones Detalladas</h5>

                                <?php if (!empty($vacaciones)): ?>
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th>
                                                <th>Días</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vacaciones as $vac): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($vac['fecha_inicio'])); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($vac['fecha_fin'])); ?></td>
                                                    <td><?php echo htmlspecialchars($vac['cantidad_dias']); ?></td>
                                                    <td><?php echo htmlspecialchars($vac['estado']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="text-muted">No registra vacaciones.</p>
                                <?php endif; ?>


                            </div>

                            <div class="mb-4">
                                <h5 class="text-success"><i class="fas fa-money-bill-wave"></i> Viáticos</h5>
                                <?php if (!empty($viaticos)): ?>
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($viaticos as $v): ?>
                                                <tr>
                                                    <td><?php echo date("d/m/Y", strtotime($v['FECHA'])); ?></td>
                                                    <td><?php echo htmlspecialchars($v['TIPO']); ?></td>
                                                    <td>$<?php echo number_format($v['MONTO'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="text-muted">No se registran viáticos.</p>
                                <?php endif; ?>
                            </div>


                            <div class="mb-4">
                                <h5 class="text-primary"><i class="fas fa-clock"></i> Horas Extras</h5>
                                <?php if (!empty($horasExtras)): ?>
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo de Hora</th>
                                                <th>Cantidad</th>
                                                <th>Tipo de Recargo</th>
                                                <th>Valor Hora Extra ($)</th>
                                                <th>Total ($)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totalHorasExtras = 0;
                                            $totalMonto = 0;
                                            foreach ($horasExtras as $he) :
                                                $subtotal = $he['cantidadHoras'] * $he['valorHoraExtra'];
                                                $totalHorasExtras += $he['cantidadHoras'];
                                                $totalMonto += $subtotal;
                                            ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($he['fecha'])); ?></td>
                                                    <td><?php echo htmlspecialchars($he['tipoHora']); ?></td>
                                                    <td><?php echo number_format($he['cantidadHoras'], 2); ?> hs</td>
                                                    <td><?php echo htmlspecialchars($he['tipoRecargo']); ?></td>
                                                    <td>$<?php echo number_format($he['valorHoraExtra'], 2); ?></td>
                                                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <td colspan="2" class="text-end"><strong>Total de Horas</strong></td>
                                                <td><strong><?php echo number_format($totalHorasExtras, 2); ?> hs</strong></td>
                                                <td colspan="2" class="text-end"><strong>Total a Pagar</strong></td>
                                                <td><strong>$<?php echo number_format($totalMonto, 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No registra Horas Extras.</p>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5 class="text-primary"><i class="fas fa-gavel"></i> Embargos Judiciales</h5>
                            <?php if (!empty($embargos)): ?>
                                <table class="table table-striped table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Fecha Registro</th>
                                            <th>Empleado</th>
                                            <th>Expediente</th>
                                            <th>Tipo</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Monto ($)</th>
                                            <th>% del Sueldo</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalMontoE = 0;
                                        foreach ($embargos as $e):
                                            // Calcular monto si solo hay porcentaje y sueldo básico disponible
                                            if (empty($e['monto']) && !empty($e['porcentaje']) && isset($e['sueldo_basico'])) {
                                                $montoCalculado = ($e['porcentaje'] / 100) * $e['sueldo_basico'];
                                            } else {
                                                $montoCalculado = $e['monto'];
                                            }
                                            $totalMontoE += $montoCalculado;
                                        ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($e['fecha'])); ?></td>
                                                <td><?php echo htmlspecialchars($e['nombre'] . ' ' . $e['apellido']); ?></td>
                                                <td><?php echo htmlspecialchars($e['expediente']); ?></td>
                                                <td><?php echo htmlspecialchars($e['tipo']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($e['fecha_inicio'])); ?></td>
                                                <td><?php echo !empty($e['fecha_fin']) ? date('d/m/Y', strtotime($e['fecha_fin'])) : 'Indefinido'; ?></td>
                                                <td>$<?php echo number_format($montoCalculado, 2); ?></td>
                                                <td><?php echo !empty($e['porcentaje']) ? $e['porcentaje'] . '%' : '-'; ?></td>
                                                <td><?php echo $e['estado'] ? 'Activo' : 'Inactivo'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="6" class="text-end"><strong>Total Embargos</strong></td>
                                            <td colspan="3"><strong>$<?php echo number_format($totalMontoE, 2); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No registra Embargos Judiciales.</p>
                            <?php endif; ?>
                        </div>



                        <div class="mb-4">
                            <h5 class="text-danger"><i class="fas fa-gavel"></i> Sanciones activas</h5>
                            <?php if (!empty($sanciones)): ?>
                                <table class="table table-bordered table-hover">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Días</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $diasSuspension = 0;
                                        foreach ($sanciones as $san): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($san['NOMBRETIPO']); ?></td>
                                                <td><?php echo htmlspecialchars($san['FECHAINICIO']); ?></td>
                                                <td><?php echo htmlspecialchars($san['FECHAFIN']); ?></td>
                                                <td><?php echo htmlspecialchars($san['DIAS']); ?></td>
                                                <td><?php echo htmlspecialchars($san['ESTADO']); ?></td>
                                            </tr>

                                        <?php $diasSuspension += (int)$san['DIAS'];
                                        endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No registra sanciones activas.</p>
                            <?php endif; ?>
                        </div>


                        <?php
                        // Total de días a descontar
                        $diasADescontar = $infoJornada['inasistencias'] + $diasSuspension;
                        $basico = $detalle['sueldo_basico'];
                        // Valor día
                        $valorDia = ($infoJornada['diasLaboralesProgramados'] > 0)
                            ? $detalle['sueldo_basico'] / $infoJornada['diasLaboralesProgramados']
                            : 0;

                        // Descuento total
                        $descuentoTotal = $valorDia * $diasADescontar;

                        // Sueldo ajustado después de descuentos por inasistencias/suspensiones (mínimo 0)
                        $sueldoAjustado = max(0, $detalle['sueldo_basico'] - $descuentoTotal);

                        // Sueldo bruto sumando horas extras
                        $sueldoBruto = $sueldoAjustado + $totalMonto;

                        // Porcentajes
                        $porcentajeObraSocial = 0.03;  // 3%
                        $porcentajeJubilacion = 0.11;  // 11%

                        // Descuentos sobre sueldo ajustado, nunca sobre negativo
                        $descuentoObraSocial = $basico * $porcentajeObraSocial;
                        $descuentoJubilacion = $basico * $porcentajeJubilacion;

                        // 7. Descuento por embargos
                        $descuentoEmbargos = 0;
                        if (!empty($embargos)) {
                            foreach ($embargos as $em) {
                                if (!empty($em['monto']) && $em['monto'] > 0) {
                                    $descuentoEmbargos += $em['monto'];
                                } elseif (!empty($em['porcentaje']) && $em['porcentaje'] > 0) {
                                    $descuentoEmbargos += $sueldoAjustado * ($em['porcentaje'] / 100);
                                }
                            }
                        }

                        // Total descuentos
                        $totalDescuentos = $descuentoTotal + $descuentoObraSocial + $descuentoJubilacion + $descuentoEmbargos;

                        // Sueldo neto final, mínimo 0
                        $sueldoNeto = max(0, $sueldoBruto - $descuentoObraSocial - $descuentoJubilacion - $descuentoEmbargos);
                        ?>


                        <div style="max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #999; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; background-color: #f9f9f9;">
                            <h3 style="text-align: center; margin-bottom: 20px;">Detalle no válido como liquidación final</h3>

                            <table style="width: 100%; border-collapse: collapse;">
                                <tbody>
                                    <tr>
                                        <td><strong>Sueldo Basico:</strong></td>
                                        <td style="text-align: right;">$<?php echo $detalle['sueldo_basico']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Días Inasistidos:</strong></td>
                                        <td style="text-align: right;"><?php echo $infoJornada['inasistencias']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Días de Suspensión:</strong></td>
                                        <td style="text-align: right;"><?php echo $diasSuspension; ?></td>
                                    </tr>
                                    <tr style="border-top: 1px solid #ccc;">
                                        <td><strong>Total días a descontar:</strong></td>
                                        <td style="text-align: right;"><?php echo $diasADescontar; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Valor por día:</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($valorDia, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #ccc;">
                                        <td><strong>Descuento total:</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($descuentoTotal, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr style="border-top: 1px solid #ccc;">
                                        <td><strong>Total Horas Extras:</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($totalMonto, 2, ',', '.'); ?></td>
                                    </tr>

                                    <tr>
                                        <td><strong>Sueldo Bruto:</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($sueldoBruto, 2, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Descuento Embargos:</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($descuentoEmbargos, 2, ',', '.'); ?></td>
                                    </tr>

                                    <tr>
                                        <td><strong>Descuento Obra Social (3%):</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($descuentoObraSocial, 2, ',', '.'); ?></td>
                                    </tr>

                                    <tr>
                                        <td><strong>Descuento Jubilación (11%):</strong></td>
                                        <td style="text-align: right;">$<?php echo number_format($descuentoJubilacion, 2, ',', '.'); ?></td>
                                    </tr>

                                    <tr style="border-top: 2px solid #333; font-weight: bold; background-color: #eaeaea;">
                                        <td>Total Descuentos:</td>
                                        <td style="text-align: right;">$<?php echo number_format($totalDescuentos, 2, ',', '.'); ?></td>
                                    </tr>

                                    <tr style="font-weight: bold; font-size: 1.1em;">
                                        <td>Sueldo Neto a Pagar:</td>
                                        <td style="text-align: right;">$<?php echo number_format($sueldoNeto, 2, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>



                        </div>


                        <a href="detalle_preliquidacion.php?id=<?php echo $idPreliquidacion; ?>" class="btn btn-secondary">Volver</a>
                    </div>

                </div>
            </div>
            </div>
        </section>
    </main>
    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>

</body>

</html>