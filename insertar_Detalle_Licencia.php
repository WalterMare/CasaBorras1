<?php 

function InsertarDetalleLicencia($vConexion){
    $documento =addslashes(file_get_contents($_FILES['documento']['tmp_name']));
  
    $SQL_Insert="INSERT INTO detallelicencia (iddetallelicencia, idLicencia,  Descripcion, Documentacion, FechaCreacion, UsuarioCreacion) 
    VALUES (null,'".$_POST['licencia']."', '".$_POST['descripcion']."' , '".$documento."', '".$_POST['fecha']."', '".$_POST['usuario']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }
    

    return true;

}
?>