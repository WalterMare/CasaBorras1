<?php
function Listar_asistenciasHoy($vConexion, $registrosPorPagina, $offset) {

    $Listado = array();

    // 1) Armo la consulta
    $consulta = "SELECT e.idempleado, e.nombre, e.apellido, a.idEmpleado, a.fecha, a.horaEntrada, a.horaSalida, a.estado, a.observacion_entrada, a.observacion_salida
    FROM empleado e
    LEFT JOIN asistencias a ON e.idempleado = a.idEmpleado AND a.fecha = CURDATE()
    ORDER BY e.idempleado
    LIMIT $registrosPorPagina OFFSET $offset";

    // 2) Ejecuto la consulta
    $rs = mysqli_query($vConexion, $consulta);

    // 3) Armo el array
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['IDEMPLEADO'] = $data['idempleado'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['FECHA'] = $data['fecha'];
        $Listado[$i]['ENTRADA'] = $data['horaEntrada'];
        $Listado[$i]['SALIDA'] = $data['horaSalida'];
        $Listado[$i]['ESTADO'] = $data['estado'];
        $Listado[$i]['OBS_ENTRADA'] = $data['observacion_entrada'];
        $Listado[$i]['OBS_SALIDA'] = $data['observacion_salida'];
        $i++;
    }

    return $Listado;
}
?>
