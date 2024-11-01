<?php
session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();

require_once 'select_embargo.php';

    
    if (Eliminar_Consulta($conexion, $_GET['ID']) != false) {
        $_SESSION['Mensaje'] .= 'Se ha eliminado el Embargo Judicial.';
        $_SESSION['Estilo'] = 'success';
    } else {
        $_SESSION['Mensaje'] .= 'No se logró eliminar el Embargo Judicial. <br />';
        $_SESSION['Estilo'] = 'warning';
    }

header('Location: Listado_Embargo_empleado.php'); //redirecciona a otra pagina
exit;
?>