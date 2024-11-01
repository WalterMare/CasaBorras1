<?php
function ModificarEmbargo($vConexion, $empleado)
{

$fecha=$_POST["fecha"];
$Monto=$_POST["monto"];
$Empleado=$_POST["empleado"];
$Descripcion=$_POST["descripcion"];


    $SQL_Insert = "UPDATE  embargo
    SET fecha='$fecha', monto='$Monto', idEmpleado='$Empleado', descripcion='$Descripcion'
    WHERE idEmpleado=$empleado";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
