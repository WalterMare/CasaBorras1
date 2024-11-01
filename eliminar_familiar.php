<?php
    session_start();
    if (empty($_SESSION['Usuario_Nombre']) ) {
        header('Location: cerrarsesion.php');
        exit;
    }
    
   //voy a necesitar la conexion: incluyo la funcion de Conexion.
require_once 'conexiondb.php';

//genero una variable para usar mi conexion desde donde me haga falta
//no envio parametros porque ya los tiene definidos por defecto
$MiConexion = ConexionBD();
   

    require_once 'select_familiar.php';

    if ( Eliminar_Familiar($MiConexion , $_GET['ID']) != false ) {
        $_SESSION['Mensaje'].='Se ha eliminado la consulta seleccionada';
        $_SESSION['Estilo']='success';
    }else {
        $_SESSION['Mensaje'].='No se pudo borrar la consulta. <br /> ';
        $_SESSION['Estilo']='warning';
    }
    
   
    header('Location: Listado_empleados.php');
    exit;
?>