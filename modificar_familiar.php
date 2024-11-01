<?php
function ModificarFamiliar($vConexion, $familiar)
{

   
$nombre=$_POST["nombre"];
$apellido=$_POST["apellido"];
$fechaNacimiento=$_POST["fechanacimiento"];
$tel=$_POST["tel"];
$dni= $_POST["documento"];
$relacion=$_POST['relacion'];
$empleado=$_POST['empleado'];

    $SQL_Insert = "UPDATE  familiar
    SET nombre='$nombre', apellido='$apellido',fechaNacimiento='$fechaNacimiento',
    tel='$tel',dni='$dni', Idrelacion='$relacion',IdEmpleado='$empleado'
    WHERE idfamiliar=$familiar";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}