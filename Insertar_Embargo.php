<?php 
function InsertarEmbargo($vConexion){
  

    $SQL_Insert="INSERT INTO embargo(idembargo, fecha, monto,  idEmpleado, descripcion) 
    VALUES (null,'".$_POST['fecha']."' ,  '".$_POST['monto']."',  '".$_POST['empleado']."', '".$_POST['descripcion']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>