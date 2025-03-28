<?php  
function InsertarVacacion($vConexion){
    // Preparamos la consulta SQL para insertar las vacaciones
    $SQL_Insert = "INSERT INTO vacaciones (idvacaciones, idempleado, fecha_inicio, fecha_fin, cantidad_dias, estado, año, vacaciones_restantes) 
                   VALUES (null, '".$_POST['empleado']."', '".$_POST['fecha_inicio']."', '".$_POST['fecha_fin']."', '".$_POST['cantidad_dias']."', '".$_POST['estado']."', '".$_POST['año']."', '".$_POST['vacaciones_restantes']."')";

    // Ejecutamos la consulta
    if (!mysqli_query($vConexion, $SQL_Insert)) {
        // Si hay un error, mostramos un mensaje de error
        die('<h4>Error al intentar insertar el registro de vacaciones.</h4>');
    }

    // Retornamos true si la inserción fue exitosa
    return true;
}
?>