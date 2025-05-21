
<?php
function obtenerInasistencias($conexion, $fechaInicio, $fechaFin)
{
    $inasistencias = [];

    // Obtener todos los empleados activos
    $sql_empleados = "SELECT idempleado FROM empleado WHERE estado = 1";
    $res_emp = mysqli_query($conexion, $sql_empleados);

    while ($emp = mysqli_fetch_assoc($res_emp)) {
        $idempleado = $emp['idempleado'];

        // Obtener días laborales del empleado (por turno)
        $sql_dias = "SELECT dia_semana FROM empleado_dia_horario WHERE idempleado = $idempleado";
        $res_dias = mysqli_query($conexion, $sql_dias);

        $dias_laborales = [];
        while ($row = mysqli_fetch_assoc($res_dias)) {
            $dias_laborales[] = $row['dia_semana']; // Ej: 'Lunes'
        }

        // Obtener fechas con asistencia
        $asistencias = [];
        $sql_asis = "SELECT fecha FROM asistencia WHERE idEmpleado = $idempleado AND fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
        $res_asis = mysqli_query($conexion, $sql_asis);
        while ($a = mysqli_fetch_assoc($res_asis)) {
            $asistencias[] = $a['fecha'];
        }

        // Obtener fechas de vacaciones
        $vacaciones = [];
        $sql_vac = "SELECT fecha_inicio, fecha_fin FROM vacaciones WHERE idempleado = $idempleado AND estado = 'aprobado' AND fecha_inicio <= '$fechaFin' AND fecha_fin >= '$fechaInicio'";
        $res_vac = mysqli_query($conexion, $sql_vac);
        while ($v = mysqli_fetch_assoc($res_vac)) {
            $rango = rangoFechas($v['fecha_inicio'], $v['fecha_fin']);
            $vacaciones = array_merge($vacaciones, $rango);
        }

        // Obtener fechas de licencia
        $licencias = [];
        $sql_lic = "SELECT fechainicio, fechafin FROM licencia WHERE idEmpleado = $idempleado AND IdEstado = 1 AND fechainicio <= '$fechaFin' AND fechafin >= '$fechaInicio'";
        $res_lic = mysqli_query($conexion, $sql_lic);
        while ($l = mysqli_fetch_assoc($res_lic)) {
            $rango = rangoFechas($l['fechainicio'], $l['fechafin']);
            $licencias = array_merge($licencias, $rango);
        }

        // Obtener fechas de sanciones
        $sanciones = [];
        $sql_san = "SELECT fecha_inicio, fecha_fin FROM sancion WHERE idEmpleado = $idempleado AND fecha_inicio <= '$fechaFin' AND fecha_fin >= '$fechaInicio'";
        $res_san = mysqli_query($conexion, $sql_san);
        while ($s = mysqli_fetch_assoc($res_san)) {
            $rango = rangoFechas($s['fecha_inicio'], $s['fecha_fin']);
            $sanciones = array_merge($sanciones, $rango);
        }

        // Generar calendario en rango de fechas
        $inasistenciasEmpleado = [];

        $periodo = new DatePeriod(
            new DateTime($fechaInicio),
            new DateInterval('P1D'),
            (new DateTime($fechaFin))->modify('+1 day')
        );

        foreach ($periodo as $fecha) {
            $daysES = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];

            $dia_nombre = $daysES[$fecha->format('l')];

            // Si ese día es laboral para el empleado
            if (in_array($dia_nombre, $dias_laborales)) {
                $fechaStr = $fecha->format('Y-m-d');

                if (
                    !in_array($fechaStr, $asistencias) &&
                    !in_array($fechaStr, $vacaciones) &&
                    !in_array($fechaStr, $licencias) &&
                    !in_array($fechaStr, $sanciones)
                ) {
                    $inasistenciasEmpleado[] = $fechaStr;
                }
            }
        }

        $inasistencias[$idempleado] = $inasistenciasEmpleado;
    }

    return $inasistencias;
}

// Función para generar rango de fechas
function rangoFechas($inicio, $fin)
{
    $rango = [];
    $current = strtotime($inicio);
    $end = strtotime($fin);
    while ($current <= $end) {
        $rango[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $rango;
}
?>