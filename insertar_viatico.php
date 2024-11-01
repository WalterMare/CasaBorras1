<?php 
function InsertarViatico($vConexion){
  

    $SQL_Insert="INSERT INTO viatico (idviatico, idTipo, idEmpleado,fechaotorgamiento,monto) 
    VALUES (null,'".$_POST['tipo']."' , '".$_POST['empleado']."' , '".$_POST['fecha']."' , '".$_POST['monto']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>