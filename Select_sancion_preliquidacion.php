<?php
/*
function Listar_Sancion_Empleado_Preliquidacion($vConexion, $empleado, $idPreliquidacion) {
    $Listado = array();
    $empleado = (int)$empleado; // proteger valor
    $idPreliquidacion = (int)$idPreliquidacion;

    // Obtener periodo de la preliquidacion
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($vConexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $resultadoPeriodo = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($resultadoPeriodo);
    if (!$filaPeriodo) {
        return $Listado; // no encontró periodo
    }

    // Extraer fechas de inicio y fin del periodo
    $partes = explode(' a ', $filaPeriodo['periodo']);
    if (count($partes) != 2) {
        return $Listado; // formato incorrecto
    }
    $fechaInicioPeriodo = $partes[0];
    $fechaFinPeriodo = $partes[1];

    // Consulta con filtro por periodo
    $consulta = "
        SELECT 
            s.fecha_inicio AS fecha_inicio_sancion,
            s.fecha_fin AS fecha_fin_sancion,
            s.cantidadDias,
            s.idsancion,
            t.nombreTipo,
            e.nombres AS estado_nombre,
            emp.nombre AS nombre_empleado,
            emp.apellido AS apellido_empleado
        FROM sancion s
        JOIN tiposancion t ON s.IdTipoSancion = t.idtiposancion
        JOIN estadosancion e ON s.idEstadoSancion = e.idestadoSancion
        JOIN empleado emp ON s.idEmpleado = emp.idempleado
        WHERE s.idEmpleado = ?
          AND s.fecha_inicio <= ?
          AND s.fecha_fin >= ?
        ORDER BY s.fecha_inicio
    ";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "iss", $empleado, $fechaFinPeriodo, $fechaInicioPeriodo);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    if (!$rs) {
        echo "Error en la consulta: " . mysqli_error($vConexion);
        return $Listado;
    }

    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[] = [
            'FECHAINICIO' => $data['fecha_inicio_sancion'],
            'FECHAFIN' => $data['fecha_fin_sancion'],
            'NOMBRE' => $data['nombre_empleado'],
            'APELLIDO' => $data['apellido_empleado'],
            'NOMBRETIPO' => $data['nombreTipo'],
            'DIAS' => $data['cantidadDias'],
            'ESTADO' => $data['estado_nombre'],
            'IDSANCION' => $data['idsancion'],
        ];
    }

    return $Listado;
}
    */
function Listar_Sancion_Empleado_Preliquidacion($vConexion, $empleado, $idPreliquidacion) {
    $Listado = [];
    $empleado = (int)$empleado;
    $idPreliquidacion = (int)$idPreliquidacion;

    // Obtener periodo
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($vConexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $rsPer = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($rsPer);

    if (!$filaPeriodo) return $Listado;

    // Fechas del período
    list($periodoInicio, $periodoFin) = explode(" a ", $filaPeriodo['periodo']);

    // Buscar sanciones que solapen con el período
    $consulta = "
        SELECT 
            s.fecha_inicio,
            s.fecha_fin,
            s.cantidadDias,
            s.idsancion,
            t.nombreTipo,
            e.nombres AS estado
        FROM sancion s
        JOIN tiposancion t ON s.IdTipoSancion = t.idtiposancion
        JOIN estadosancion e ON s.idEstadoSancion = e.idestadoSancion
        WHERE s.idEmpleado = ?
          AND s.fecha_inicio <= ?
          AND s.fecha_fin >= ?
        ORDER BY s.fecha_inicio
    ";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "iss", $empleado, $periodoFin, $periodoInicio);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($data = mysqli_fetch_assoc($rs)) {

        // SOLO sanciones de tipo Suspensión
        if (strtolower($data['nombreTipo']) == 'Suspensión') continue;

        // Calcular días dentro del período
        $diasPeriodo = Dias_En_Periodo(
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $periodoInicio,
            $periodoFin
        );

        $Listado[] = [
            'FECHAINICIO' => $data['fecha_inicio'],
            'FECHAFIN' => $data['fecha_fin'],
            'NOMBRETIPO' => $data['nombreTipo'],
            'DIAS_TOTALES' => $data['cantidadDias'],
            'DIAS_PERIODO' => $diasPeriodo-1,
            'ESTADO' => $data['estado'],
            'IDSANCION' => $data['idsancion'],
        ];
    }

    return $Listado;
}


function Dias_En_Periodo($inicio, $fin, $periodoInicio, $periodoFin)
{
    // Normalizar fechas
    $inicio = new DateTime($inicio);
    $fin = new DateTime($fin);
    $periodoInicio = new DateTime($periodoInicio);
    $periodoFin = new DateTime($periodoFin);

    // Calcular solapamiento
    $inicioReal = ($inicio > $periodoInicio) ? $inicio : $periodoInicio;
    $finReal = ($fin < $periodoFin) ? $fin : $periodoFin;

    // Si no hay rango válido → 0
    if ($finReal < $inicioReal) return 0;

    return $inicioReal->diff($finReal)->days + 1;
}

?>