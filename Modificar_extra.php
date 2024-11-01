<?php
function ModificarExtra($vConexion, $empleado)
{

$fecha=$_POST["fecha"];
$Horas=$_POST["horas"];
$Empleado=$_POST["empleado"];

    $SQL_Insert = "UPDATE  horaextra
    SET fecha='$fecha', cantidadHoras='$Horas', IdEmpleado='$Empleado'
    WHERE IdEmpleado=$empleado";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
