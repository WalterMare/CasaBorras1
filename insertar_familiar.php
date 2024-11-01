<?php 
function InsertarFamiliar($vConexion){
  

    

    $SQL_Insert="INSERT INTO familiar(idfamiliar,apellido, nombre, dni, Idrelacion, fechaNacimiento,tel,IdEmpleado) 
    VALUES (null,'".$_POST['apellido']."' , '".$_POST['nombre']."' , '".$_POST['dni']."' , '".$_POST['relacion']."','".$_POST['fechanacimiento']."','".$_POST['tel']."','".$_POST['empleado']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>