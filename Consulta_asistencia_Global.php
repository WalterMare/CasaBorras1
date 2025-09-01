<?php

function Obtener_Resumen_Asistencia_Completo($vConexion, $fechaInicio, $fechaFin, $empleado = '', $dni = '', $ordenColumna = 'apellido', $ordenDireccion = 'ASC')
{
    // Columnas permitidas para ordenar (prevención SQL injection)
    $columnasPermitidas = [
        'empleado' => 'e.apellido',
        'dni' => 'e.dni',
        'diasLaboralesProgramados' => 'diasLaboralesProgramados',
        'diasTrabajados' => 'diasTrabajados',
        'inasistencias' => 'inasistencias',
        'porcentajeAsistencia' => 'porcentajeAsistencia'
    ];

    $direccion = strtoupper($ordenDireccion) === 'DESC' ? 'DESC' : 'ASC';
    $columna = $columnasPermitidas[$ordenColumna] ?? 'e.apellido';

    $query = "
        SELECT e.idEmpleado, CONCAT(e.apellido, ', ', e.nombre) AS empleado, e.dni
        FROM empleado e
        INNER JOIN asistencia a ON a.idEmpleado = e.idEmpleado
        WHERE 1=1
    ";

    $params = [];
    $tipos = '';

    // Filtros
    if (!empty($empleado)) {
        $query .= " AND (e.nombre LIKE ? OR e.apellido LIKE ?) ";
        $tipos .= 'ss';
        $params[] = "%$empleado%";
        $params[] = "%$empleado%";
    }
    if (!empty($dni)) {
        $query .= " AND e.dni LIKE ? ";
        $tipos .= 's';
        $params[] = "%$dni%";
    }

    $query .= " GROUP BY e.idEmpleado, e.apellido, e.nombre, e.dni
            ";

    $stmt = mysqli_prepare($vConexion, $query);
    if (!empty($params)) mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $resumen = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // Para cada empleado calculamos asistencia real usando fechas de filtro
        $detalles = Obtener_Detalles_Jornada_Empleado_Fecha($vConexion, $fila['idEmpleado'], $fechaInicio, $fechaFin);

        $fila['diasLaboralesProgramados'] = $detalles['diasLaboralesProgramados'];
        $fila['diasTrabajados'] = $detalles['diasTrabajados'];
        $fila['inasistencias'] = $detalles['inasistencias'];
        $fila['porcentajeAsistencia'] = $detalles['diasLaboralesProgramados'] > 0
            ? round(($detalles['diasTrabajados'] * 100) / $detalles['diasLaboralesProgramados'], 2)
            : 0;

        $resumen[] = $fila;
    }
    usort($resumen, function ($a, $b) use ($ordenColumna, $ordenDireccion) {
        $dir = strtoupper($ordenDireccion) === 'DESC' ? -1 : 1;
        switch ($ordenColumna) {
            case 'empleado':
            case 'dni':
                return $dir * strcmp($a[$ordenColumna], $b[$ordenColumna]);
            case 'diasTrabajados':
            case 'diasLaboralesProgramados':
            case 'inasistencias':
            case 'porcentajeAsistencia':
                return $dir * ($a[$ordenColumna] <=> $b[$ordenColumna]);
            default:
                return 0;
        }
    });

    mysqli_stmt_close($stmt);
    return $resumen;
}

function Obtener_Detalles_Jornada_Empleado_Fecha($vConexion, $idEmpleado, $fechaInicio, $fechaFin)
{
    // 1. Calcular días del periodo
    $fechaInicioDT = new DateTime($fechaInicio);
    $fechaFinDT = new DateTime($fechaFin);
    $intervalo = $fechaInicioDT->diff($fechaFinDT);
    $diasDelPeriodo = $intervalo->days + 1;

    // 2. Contar días laborales programados
    $diasLaboralesProgramados = contarDiasLaboralesProgramados($idEmpleado, $fechaInicio, $fechaFin, $vConexion);

    // 3. Estados que cuentan como trabajados
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
    mysqli_stmt_bind_param($stmt2, $tipos, $idEmpleado, $fechaInicio, $fechaFin, ...$estadosValidos);
    mysqli_stmt_execute($stmt2);
    $resultado2 = mysqli_stmt_get_result($stmt2);
    $fila2 = mysqli_fetch_assoc($resultado2);
    $diasTrabajados = $fila2['diasTrabajados'] ?? 0;

    // 4. Inasistencias
    $inasistencias = $diasLaboralesProgramados - $diasTrabajados;

    return [
        'diasPeriodo' => $diasDelPeriodo,
        'diasLaboralesProgramados' => $diasLaboralesProgramados,
        'diasTrabajados' => $diasTrabajados,
        'inasistencias' => $inasistencias
    ];
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
