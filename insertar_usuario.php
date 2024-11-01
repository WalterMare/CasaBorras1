<?php 
function InsertarUsuario($vConexion){
  

    

    $SQL_Insert="INSERT INTO usuario(idusuario,user, clave, IdEmpleado, Idtipo) 
    VALUES (null,'".$_POST['usuario']."' , '".$_POST['clave']."' , '".$_POST['empleado']."' , '".$_POST['tipo']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>