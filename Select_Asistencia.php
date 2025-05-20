<?php
function Listar_asistenciasHoy($conexion, $limit, $offset) {
    $fechaHoy = date('Y-m-d');
    $query = "SELECT 
                a.idEmpleado AS IDEMPLEADO,
                e.nombre AS NOMBRE,
                e.apellido AS APELLIDO,
                a.horaEntrada AS ENTRADA,
                a.horaSalida AS SALIDA,
                a.estado AS ESTADO,
                a.observacion_entrada AS OBS_ENTRADA,
                a.observacion_salida AS OBS_SALIDA
              FROM asistencia a
              INNER JOIN empleado e ON a.idEmpleado = e.idEmpleado
              WHERE a.fecha = ?
              ORDER BY a.horaEntrada ASC
              LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'sii', $fechaHoy, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $datos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $datos[] = $fila;
    }
    return $datos;
}
?>

