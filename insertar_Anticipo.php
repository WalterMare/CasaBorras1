<?php 
function InsertarAnticipo($vConexion){
  

    $SQL_Insert="INSERT INTO anticipo (idanticipo, IdEmpleado, fechaOtorgamiento, monto, usuario) 
    VALUES (null,'".$_POST['empleado']."' , '".$_POST['fecha']."' , '".$_POST['monto']."', '".$_POST['otorgante']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>