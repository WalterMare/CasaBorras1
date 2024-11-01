<?php 
function InsertarHoraExtra($vConexion){
  

    $SQL_Insert="INSERT INTO horaextra(idhoraextra, fecha, cantidadHoras,  IdEmpleado) 
    VALUES (null,'".$_POST['fecha']."' , '".$_POST['horas']."' , '".$_POST['empleado']."')";


    if (!mysqli_query($vConexion, $SQL_Insert)) {
        //si surge un error, finalizo la ejecucion del script con un mensaje
        die('<h4>Error al intentar insertar el registro.</h4>');
    }

    return true;
}
?>