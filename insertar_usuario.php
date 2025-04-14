<?php 
function InsertarUsuario($vConexion){
  

    $claveHash = password_hash($_POST['clave'], PASSWORD_ARGON2I, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 2
    ]);

    $SQL_Insert="INSERT INTO usuario(idusuario,user, clave, IdEmpleado, Idtipo) 
    VALUES (null,'".$_POST['usuario']."' , '".$claveHash."' , '".$_POST['empleado']."' , '".$_POST['tipo']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>