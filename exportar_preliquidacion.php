<?php
ob_start();
require 'vendor/autoload.php'; // PhpSpreadsheet
require_once 'conexiondb.php';

$conexion = ConexionBD();

require_once 'Select_tipoLicencia.php';
require_once 'Select_sancion_preliquidacion.php';
require_once 'select_viatico_preliquidacion.php';


if (!isset($_GET['id'])) {
    die("Error: ID de preliquidación no especificado.");
}
$idPreliquidacion = intval($_GET['id']);

// --- obtener periodo de la preliquidacion para usar en todo el export ---
$consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
$stmtPeriodo = mysqli_prepare($conexion, $consultaPeriodo);
mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
mysqli_stmt_execute($stmtPeriodo);
$rsPeriodo = mysqli_stmt_get_result($stmtPeriodo);
$filaPeriodo = mysqli_fetch_assoc($rsPeriodo);

if (!$filaPeriodo || empty($filaPeriodo['periodo'])) {
    throw new Exception("Período inválido");
}

list($periodoInicio, $periodoFin) = array_map('trim', explode(' a ', $filaPeriodo['periodo']));



// --- 1. Consultar todos los empleados de la preliquidación ---
$consultaEmpleados = "SELECT dp.idEmpleado, e.nombre, e.apellido, e.dni
                      FROM detallepreliquidacion dp
                      INNER JOIN empleado e ON dp.idEmpleado = e.idempleado
                      WHERE dp.idPreliquidacion = ?";
$stmtEmp = mysqli_prepare($conexion, $consultaEmpleados);
mysqli_stmt_bind_param($stmtEmp, "i", $idPreliquidacion);
mysqli_stmt_execute($stmtEmp);
$resultEmp = mysqli_stmt_get_result($stmtEmp);
$empleados = [];
while ($fila = mysqli_fetch_assoc($resultEmp)) {
    $empleados[] = $fila;
}

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
        return [];
    }

    // Separar las fechas de inicio y fin (formato 'YYYY-MM-DD a YYYY-MM-DD')
    list($fechaInicioPeriodo, $fechaFinPeriodo) = explode(' a ', $filaPeriodo['periodo']);

    // Consultar vacaciones que se cruzan con el período
    $consultaVacaciones = "
        SELECT fecha_inicio, fecha_fin, cantidad_dias, estado
        FROM vacaciones
        WHERE idempleado = ?
        AND estado = 'Aprobado'
        AND fecha_inicio <= ?
        AND fecha_fin >= ?
        ORDER BY fecha_inicio DESC
    ";

    $stmtVac = mysqli_prepare($vConexion, $consultaVacaciones);
    mysqli_stmt_bind_param($stmtVac, "iss", $idEmpleado, $fechaFinPeriodo, $fechaInicioPeriodo);
    mysqli_stmt_execute($stmtVac);
    $resultadoVac = mysqli_stmt_get_result($stmtVac);

    $vacaciones = [];
    $totalDiasAplicables = 0;

    while ($fila = mysqli_fetch_assoc($resultadoVac)) {

        // Calcular solapamiento real entre vacaciones y período
        $inicioVac = $fila['fecha_inicio'];
        $finVac = $fila['fecha_fin'];

        // El inicio aplicable es el más reciente
        $inicioAplicable = max($inicioVac, $fechaInicioPeriodo);

        // El fin aplicable es el más temprano
        $finAplicable = min($finVac, $fechaFinPeriodo);

        // Cálculo de días dentro del período
        if ($inicioAplicable <= $finAplicable) {
            $dias = (strtotime($finAplicable) - strtotime($inicioAplicable)) / 86400 + 1;
        } else {
            $dias = 0;
        }

        // Acumular totales
        $totalDiasAplicables += $dias;

        // Guardar detalle con días dentro del período
        $fila['dias_en_periodo'] = $dias;

        $vacaciones[] = $fila;
    }

    return [
        'detalle' => $vacaciones,
        'dias_vacaciones' => $totalDiasAplicables
    ];
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

// Crear nuevo archivo Excel
$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

// --- HOJA 1: Resumen Empleados ---
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Resumen Empleados');

// Cabeceras
$headers = ['Nombre', 'Apellido', 'DNI', 'Cargo', 'Sueldo Básico', 'Fecha Inicio', 'Antigüedad', 'Estado Civil', 'Hijos a cargo', 'Días Laborables', 'Días Trabajados', 'Inasistencias'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Filas
$row = 2;
$antiguedad = 0;
foreach ($empleados as $emp) {
    // Detalles generales
    $detalle = Obtener_Detalles_Empleado($conexion, $emp['idEmpleado'], $idPreliquidacion);
    $infoJornada = Obtener_Detalles_Jornada_Empleado($conexion, $emp['idEmpleado'], $idPreliquidacion);
    // Calcular antigüedad en años
    if (!empty($detalle['fecha_inicio'])) {
        $fechaInicio = new DateTime($detalle['fecha_inicio']);
        $hoy = new DateTime();
        $antiguedad = $fechaInicio->diff($hoy)->y; // años completos
    } else {
        $antiguedad = 0;
    }

    $sheet->setCellValue('A' . $row, $detalle['nombre']);
    $sheet->setCellValue('B' . $row, $detalle['apellido']);
    $sheet->setCellValue('C' . $row, $detalle['dni']);
    $sheet->setCellValue('D' . $row, $detalle['cargo']);
    $sheet->setCellValue('E' . $row, $detalle['sueldo_basico']);
    $sheet->getStyle('E' . $row)
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');
    $sheet->setCellValue('F' . $row, $detalle['fecha_inicio']);
    $sheet->setCellValue('G' . $row, $antiguedad);
    $sheet->setCellValue('H' . $row, $detalle['estado_civil']);
    $sheet->setCellValue('I' . $row, $detalle['cantidad_hijos']);
    $sheet->setCellValue('J' . $row, $infoJornada['diasLaboralesProgramados']);
    $sheet->setCellValue('K' . $row, $infoJornada['diasTrabajados']);
    $sheet->setCellValue('L' . $row, $infoJornada['inasistencias']);
    $row++;
}

// --- HOJA 2: Horas Extras ---
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Horas Extras');
$headers = ['Empleado', 'Fecha', 'Tipo de Hora', 'Cantidad', 'Tipo Recargo', 'Valor Hora', 'Total'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}
$row = 2;
foreach ($empleados as $emp) {
    $horasExtras = Obtener_Horas_Extras_Empleado($conexion, $emp['idEmpleado'], $idPreliquidacion);
    foreach ($horasExtras as $he) {
        $totalHE = $he['cantidadHoras'] * $he['valorHoraExtra'];
        $sheet->setCellValue('A' . $row, $emp['nombre'] . ' ' . $emp['apellido']);
        $sheet->setCellValue('B' . $row, $he['fecha']);
        $sheet->setCellValue('C' . $row, $he['tipoHora']);
        $sheet->setCellValue('D' . $row, $he['cantidadHoras']);
        $sheet->setCellValue('E' . $row, $he['tipoRecargo']);
        $sheet->setCellValue('F' . $row, $he['valorHoraExtra']);
        $sheet->getStyle('F' . $row)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $sheet->setCellValue('G' . $row, $totalHE);
        $sheet->getStyle('G' . $row)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $row++;
    }
}

// --- HOJA 3: Licencias ---
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Licencias');
$headers = ['Empleado', 'Tipo', 'Inicio', 'Fin', 'Días en Período', 'Estado'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

$row = 2;

foreach ($empleados as $emp) {

    $licencias = Listar_Licencia_Empleado_preliquidacion(
        $conexion,
        $emp['idEmpleado'],
        $idPreliquidacion
    );

    foreach ($licencias as $lic) {

        // --- FECHAS DEL PERÍODO ---
        $inicioPeriodo = new DateTime($periodoInicio);   // ej: 2024-11-01
        $finPeriodo    = new DateTime($periodoFin);      // ej: 2024-11-30

        // --- FECHAS DE LA LICENCIA ---
        $inicioLic = new DateTime($lic['FECHAINICIO']);
        $finLic    = new DateTime($lic['FECHAFIN']);

        // --- RECORTE DE FECHAS QUE CAEN FUERA DEL PERÍODO ---
        $inicioReal = ($inicioLic < $inicioPeriodo) ? clone $inicioPeriodo : clone $inicioLic;
        $finReal    = ($finLic > $finPeriodo) ? clone $finPeriodo : clone $finLic;

        // --- CÁLCULO DE DÍAS DENTRO DEL PERÍODO ---
        $dias = 0;
        if ($inicioReal <= $finReal) {
            $dias = $inicioReal->diff($finReal)->days + 1;
        }

        // SI NO HAY DÍAS EN ESTE PERÍODO, NO LO MOSTRAMOS
        if ($dias <= 0) continue;

        // --- CARGA EN EXCEL ---
        $sheet->setCellValue('A' . $row, $emp['nombre'] . ' ' . $emp['apellido']);
        $sheet->setCellValue('B' . $row, $lic['NOMBRETIPO']);
        $sheet->setCellValue('C' . $row, $inicioReal->format('Y-m-d'));
        $sheet->setCellValue('D' . $row, $finReal->format('Y-m-d'));
        $sheet->setCellValue('E' . $row, $dias);
        $sheet->setCellValue('F' . $row, $lic['ESTADO']);

        $row++;
    }
}



// --- HOJA 4: Vacaciones ---
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Vacaciones');

$headers = ['Empleado', 'Inicio', 'Fin', 'Días dentro del periodo', 'Estado'];
$col = 'A';

foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

$row = 2;

foreach ($empleados as $emp) {

    $vacData = Obtener_Vacaciones_Empleado_Por_Periodo($conexion, $emp['idEmpleado'], $idPreliquidacion);

    // Si no hay detalle, pasar al próximo empleado
    if (empty($vacData['detalle'])) {
        continue;
    }

    foreach ($vacData['detalle'] as $vac) {

        // Solo mostrar si tiene días dentro del período
        if ($vac['dias_en_periodo'] <= 0) {
            continue;
        }

        $sheet->setCellValue('A' . $row, $emp['nombre'] . ' ' . $emp['apellido']);
        $sheet->setCellValue('B' . $row, $vac['fecha_inicio']);
        $sheet->setCellValue('C' . $row, $vac['fecha_fin']);
        $sheet->setCellValue('D' . $row, $vac['dias_en_periodo']);  // SOLO días dentro del período
        $sheet->setCellValue('E' . $row, $vac['estado']);

        $row++;
    }
}

// --- HOJA 5: Viáticos ---
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Viaticos');
$headers = ['Empleado', 'Fecha', 'Tipo', 'Monto'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}
$row = 2;
foreach ($empleados as $emp) {
    $viaticos = Listar_Viaticos_Empleado($conexion, $emp['idEmpleado'], $idPreliquidacion);
    foreach ($viaticos as $v) {
        $sheet->setCellValue('A' . $row, $emp['nombre'] . ' ' . $emp['apellido']);
        $sheet->setCellValue('B' . $row, $v['FECHA']);
        $sheet->setCellValue('C' . $row, $v['TIPO']);
        $sheet->setCellValue('D' . $row, $v['MONTO']);
        $sheet->getStyle('D' . $row)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $row++;
    }
}

// --- HOJA 6: Sanciones ---
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Sanciones');

$headers = ['Empleado', 'Tipo', 'Inicio', 'Fin', 'Días en Período', 'Estado'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

$row = 2;

foreach ($empleados as $emp) {

    // <<<--- SOLO CAMBIA LA FUNCIÓN
    $sanciones = Listar_Sancion_Empleado_Preliquidacion(
        $conexion,
        $emp['idEmpleado'],
        $idPreliquidacion
    );

    foreach ($sanciones as $san) {

        // --- FECHAS DEL PERÍODO ---
        $inicioPeriodo = new DateTime($periodoInicio);
        $finPeriodo    = new DateTime($periodoFin);

        // --- FECHAS DE LA SANCIÓN ---
        $inicioSan = new DateTime($san['FECHAINICIO']);
        $finSan    = new DateTime($san['FECHAFIN']);

        // --- SOLO MOSTRAR SI ES SUSPENSIÓN ---
        if (strtolower($san['NOMBRETIPO']) == 'Suspensión') {
            continue;
        }

        // --- RECORTE IGUAL QUE LICENCIAS ---
        $inicioReal = ($inicioSan < $inicioPeriodo) ? clone $inicioPeriodo : clone $inicioSan;
        $finReal    = ($finSan > $finPeriodo) ? clone $finPeriodo : clone $finSan;

        // --- CÁLCULO DE DÍAS DENTRO DEL PERÍODO ---
        $dias = 0;
        if ($inicioReal <= $finReal) {
            $dias = $inicioReal->diff($finReal)->days + 1;
        }

        // SI NO HAY DÍAS DE SUSPENSIÓN EN ESTE PERÍODO, NO SE MUESTRA
        if ($dias <= 0) continue;

        // --- CARGA EN EXCEL ---
        $sheet->setCellValue('A' . $row, $emp['nombre'] . ' ' . $emp['apellido']);
        $sheet->setCellValue('B' . $row, $san['NOMBRETIPO']);
        $sheet->setCellValue('C' . $row, $inicioReal->format('Y-m-d'));
        $sheet->setCellValue('D' . $row, $finReal->format('Y-m-d'));
        $sheet->setCellValue('E' . $row, $dias);
        $sheet->setCellValue('F' . $row, $san['ESTADO']);

        $row++;
    }
}


// --- HOJA 7: Embargos ---
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Embargos');
$headers = ['Empleado', 'Fecha', 'Expediente', 'Tipo', 'Inicio', 'Fin', 'Monto', '% Sueldo', 'Estado'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}
$row = 2;
foreach ($empleados as $emp) {
    $embargos = Listar_Embargos_Empleado($conexion, $emp['idEmpleado'], $idPreliquidacion);
    foreach ($embargos as $emb) {
        $sheet->setCellValue('A' . $row, $emp['nombre'] . ' ' . $emp['apellido']);
        $sheet->setCellValue('B' . $row, $emb['fecha']);
        $sheet->setCellValue('C' . $row, $emb['expediente']);
        $sheet->setCellValue('D' . $row, $emb['tipo']);
        $sheet->setCellValue('E' . $row, $emb['fecha_inicio']);
        $sheet->setCellValue('F' . $row, $emb['fecha_fin']);
        $sheet->setCellValue('G' . $row, $emb['monto']);
        $sheet->getStyle('G' . $row)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $sheet->setCellValue('H' . $row, $emb['porcentaje']);
        $sheet->setCellValue('I' . $row, $emb['estado']);
        $row++;
    }
}

// --- Exportar ---
$nombreArchivo = "Preliquidacion_{$idPreliquidacion}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$nombreArchivo\"");
header('Cache-Control: max-age=0');

$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
while (ob_get_level()) {
    ob_end_clean();
}
$writer->save('php://output');
exit;
