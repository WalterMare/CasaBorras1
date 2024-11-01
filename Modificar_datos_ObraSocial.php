<?php
function ModificarObra($vConexion, $empleado)
{

$nombre=$_POST["nombre"];
$Empleado=$_POST["empleado"];

    $SQL_Insert = "UPDATE  obrasocial
    SET descripcion='$nombre',  idEmpleado='$Empleado'
    WHERE idEmpleado=$empleado";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}