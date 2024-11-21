<?php

function InsertarReporte($vConexion) {
    // Obtener los valores del formulario (usamos $_POST para obtener los valores)
    $fecha = $_POST['fecha'];
    $periodoInicio = $_POST['fechainicio'];
    $periodoFin = $_POST['fechafin'];
    $idTipoReporte = $_POST['tipo'];
    $idEmpleado = $_POST['empleado'];

    // Sentencia SQL para insertar el reporte
    $SQL_Insert = "INSERT INTO reporte (fecha, idTipoReporte, idEmpleado, periodoInicio, periodoFin) 
                   VALUES (?, ?, ?, ?, ?)";

    // Preparar la consulta
    if ($stmt = mysqli_prepare($vConexion, $SQL_Insert)) {
        // Enlazar los parámetros a la consulta (s - string, i - integer)
        mysqli_stmt_bind_param($stmt, "sssss", $fecha, $idTipoReporte, $idEmpleado, $periodoInicio, $periodoFin);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            // Si la inserción fue exitosa, retornar true
            return true;
        } else {
            // Si ocurre un error al ejecutar, devolver un mensaje de error
            die('<h4>Error al intentar insertar el registro. ' . mysqli_error($vConexion) . '</h4>');
        }
        
        // Cerrar la declaración preparada
        mysqli_stmt_close($stmt);
    } else {
        // Si no se puede preparar la consulta, mostrar un mensaje de error
        die('<h4>Error al preparar la consulta.</h4>');
    }
}

?>

