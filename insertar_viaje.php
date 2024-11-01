<?php 
function InsertarViaje($vConexion){
  

    $SQL_Insert="INSERT INTO viajes(id,IdUsuarios, IdTransporte, FechaPautada, IdDestino, FechaCreacionViaje,IdUsuarioRegistra,Costo,Porcentaje) 
    VALUES (null,'".$_POST['usuario']."' , '".$_POST['transporte']."' , '".$_POST['fecha']."' , '".$_POST['destino']."',NOW(),'".$_POST['usuarioregistro']."','".$_POST['costo']."','".$_POST['porcentaje']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>