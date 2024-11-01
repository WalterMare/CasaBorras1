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

require_once 'select_mostrardatos.php';


if (!empty($_GET['ID']) && $_GET['ID'] > 0) {
    $Activation = $_GET['ESTADO'] == 1 ? 0 : 1;
    
    if (Modificar_Estado_Usuario($_GET['ID'], $Activation, $conexion) != false) {
        $_SESSION['Mensaje'] .= 'Se ha modificado el estado del usuario. <br />';
        $_SESSION['Estilo'] = 'success';
    } else {
        $_SESSION['Mensaje'] .= 'No se logró modificar el estado del usuario. <br />';
        $_SESSION['Estilo'] = 'warning';
    }
}
header('Location: Listado_empleados.php'); //redirecciona a otra pagina
exit;
?>

