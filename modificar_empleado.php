<?php
function ModificarEmpleado($vConexion, $empleado)
{

    $default = 0;
    $variable = $_POST['estado']; //operador ??, comprueba Si la variable existe y no es nula se muestra su valor.
    if ($variable == 0 || $variable == null) {
        $default = 0;
    } else {
        $default = 1;
    }
$nombre=$_POST["nombre"];
$apellido=$_POST["apellido"];
$Idsexo=$_POST["sexo"];
$fechaNacimiento=$_POST["fechanacimiento"];
$IdestadoCivil=$_POST["estadocivil"];
$email=$_POST["email"];
$estado=$default;
$fecha_inicio=$_POST["fechainicio"];
$Idcargo=$_POST["cargo"];
$direccion=$_POST["direccion"];
$ciudad=$_POST["ciudad"];
$tel=$_POST["tel"];
$idprovincia=$_POST["provincia"];

$imagen=addslashes(file_get_contents($_FILES['imagen']['tmp_name']));

$dni= $_POST["documento"];

    $SQL_Insert = "UPDATE  empleado 
    SET nombre='$nombre', apellido='$apellido', Idsexo='$Idsexo',fechaNacimiento='$fechaNacimiento',
    IdestadoCivil='$IdestadoCivil', email='$email', estado='$estado', fecha_inicio='$fecha_inicio', 
    Idcargo='$Idcargo', direccion='$direccion',ciudad='$ciudad',tel='$tel',idprovincia='$idprovincia',
    imagen='$imagen', dni='$dni'   
    WHERE idempleado=$empleado";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
