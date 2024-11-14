<?php

function Listar_ultimosEmpleados($vConexion, $tiempo, $estado) {
    $Listado = array();

    // Si el estado es vacío, no aplicamos filtro en la consulta (ambos estados)
    if ($estado =='2') {
        // Si no hay filtro de estado, mostramos todos los empleados dentro del rango de tiempo
        $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, C.descripcion
                     FROM empleado AS E, cargo AS C
                     WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL $tiempo YEAR) 
                     AND E.Idcargo = C.idcargo
                     ORDER BY E.fecha_inicio";
    } else {
        // Si hay filtro de estado (activo o inactivo), aplicamos el filtro
        $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, C.descripcion
                     FROM empleado AS E, cargo AS C
                     WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL $tiempo YEAR) 
                     AND E.estado = $estado
                     AND E.Idcargo = C.idcargo
                     ORDER BY E.fecha_inicio";
    }

    // Ejecuto la consulta
    $rs = mysqli_query($vConexion, $consulta);

    // Recorro los resultados
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['FECHA_INICIO'] = date("d/m/Y", strtotime($data['fecha_inicio']));
        $Listado[$i]['ESTADO'] = $data['estado'];
        $Listado[$i]['CARGO'] = $data['descripcion'];
        $i++;
    }

    return $Listado;
}



function Listar_ultimosEmpleados2($vConexion, $tiempo) {
    $Listado = array();

    // Usamos preparación de consultas para evitar SQL Injection
    $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, C.descripcion 
                 FROM empleado AS E 
                 JOIN cargo AS C ON E.Idcargo = C.idcargo 
                 WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL ? YEAR) 
                 ORDER BY E.fecha_inicio";

    // Preparamos la consulta
    if ($stmt = mysqli_prepare($vConexion, $consulta)) {
        // Enlazamos el parámetro
        mysqli_stmt_bind_param($stmt, 'i', $tiempo);

        // Ejecutamos la consulta
        mysqli_stmt_execute($stmt);

        // Resultados
        $result = mysqli_stmt_get_result($stmt);

        // Recorremos los resultados y los agregamos al array
        $i = 0;
        while ($data = mysqli_fetch_array($result)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['FECHA_INICIO'] = date("d/m/Y", strtotime($data['fecha_inicio']));
            $Listado[$i]['ESTADO'] = $data['estado'];
            $Listado[$i]['CARGO'] = $data['descripcion'];
            $i++;
        }

        // Cerramos la declaración
        mysqli_stmt_close($stmt);
    }

    // Retornamos el listado
    return $Listado;
}
?>