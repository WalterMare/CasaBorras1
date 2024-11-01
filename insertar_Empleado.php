<?php 
function InsertarEmpleado($vConexion){
  
    $default=0;
    $variable=$_POST['estado']; //operador ??, comprueba Si la variable existe y no es nula se muestra su valor.
    if ($variable==0 || $variable==null){
        $default=0;
    }else{
        $default=1;
    }
    
    $Imagen =addslashes(file_get_contents($_FILES['imagen']['tmp_name'])); //transforma la imagen en bits

    $SQL_Insert="INSERT INTO empleado (idempleado,nombre, apellido, Idsexo, fechaNacimiento, IdestadoCivil, email, estado,fecha_inicio, Idcargo, direccion,ciudad, tel, idprovincia, imagen,dni) 
    VALUES (null,'".$_POST['nombre']."' , '".$_POST['apellido']."' , '".$_POST['sexo']."' , '".$_POST['fechanacimiento']."','".$_POST['estadocivil']."','".$_POST['email']."','".$default."','".$_POST['fechainicio']."','".$_POST['cargo']."','".$_POST['direccion']."','".$_POST['ciudad']."','".$_POST['tel']."','".$_POST['provincia']."','".$Imagen."','".$_POST['documento']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>