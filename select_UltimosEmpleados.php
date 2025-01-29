<?php

function Listar_ultimosEmpleados($vConexion, $tiempo, $estado)
{
    $Listado = array();

    // Establecer la consulta según el estado
    if ($estado == '1') {
        // Consulta empleados activos
        $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, E.fecha_baja, C.descripcion
                     FROM empleado AS E
                     JOIN cargo AS C ON E.Idcargo = C.idcargo
                     WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
                     AND E.estado = 1
                     ORDER BY E.fecha_inicio";
    } else if ($estado == '4') {
        // Consulta empleados inactivos (sin baja)
        $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, E.fecha_baja, C.descripcion
                     FROM empleado AS E
                     JOIN cargo AS C ON E.Idcargo = C.idcargo
                     WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
                     AND E.estado = 0
                     AND E.fecha_baja IS NULL   -- Excluye los empleados dados de baja
                     ORDER BY E.fecha_inicio";
    } else if ($estado == '2') {
        // Consulta empleados activos e inactivos sin baja
        $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, E.fecha_baja, C.descripcion
                     FROM empleado AS E
                     JOIN cargo AS C ON E.Idcargo = C.idcargo
                     WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
                     AND E.estado IN (0, 1)  -- Activos e inactivos
                     AND E.fecha_baja IS NULL  -- Excluye los empleados dados de baja
                     ORDER BY E.fecha_inicio";
    } else if ($estado == '3') {
        // Consulta empleados inactivos por baja
        $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, E.fecha_baja, C.descripcion
                     FROM empleado AS E
                     JOIN cargo AS C ON E.Idcargo = C.idcargo
                     WHERE E.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
                     AND E.estado = 0
                     AND E.fecha_baja IS NOT NULL
                     ORDER BY E.fecha_inicio";
    }

    // Preparar y ejecutar la consulta
    $stmt = mysqli_prepare($vConexion, $consulta);
    $stmt = mysqli_prepare($vConexion, $consulta);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . mysqli_error($vConexion));
    }

    // ⚠️ Convertir $tiempo a entero antes de vincularlo
    $tiempo = (int) $tiempo;

    if (!mysqli_stmt_bind_param($stmt, "i", $tiempo)) {
        die("Error en la vinculación del parámetro: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_execute($stmt);

    // Obtener los resultados
    $rs = mysqli_stmt_get_result($stmt);

    // Recorrer los resultados y almacenarlos en $Listado
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['FECHA_INICIO'] = date("d/m/Y", strtotime($data['fecha_inicio']));

        // Verificar si la fecha de baja es NULL
        // Si la fecha_baja es NULL, se asigna 'N/A' (no aplica fecha de baja)
        $Listado[$i]['FECHA_BAJA'] = $data['fecha_baja'] ? date("d/m/Y", strtotime($data['fecha_baja'])) : 'N/A';

        $Listado[$i]['ESTADO'] = $data['estado'];
        $Listado[$i]['CARGO'] = $data['descripcion'];
        $i++;
    }

    // Cerrar el prepared statement
    mysqli_stmt_close($stmt);

    // Retornar el listado de empleados
    return $Listado;
}



function Listar_ultimosEmpleados2($vConexion, $tiempo)
{
    $Listado = array();

    // Usamos preparación de consultas para evitar SQL Injection
    $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado,E.fecha_baja, C.descripcion 
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
            $Listado[$i]['FECHA_BAJA'] = date("d/m/Y", strtotime($data['fecha_baja']));
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
