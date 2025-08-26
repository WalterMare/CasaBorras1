<?php
session_start();

// Redirecciona al login si no hay sesión activa
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();
require_once 'select_embargo.php';

// Validar que se envió un ID válido
if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) {
    $_SESSION['Mensaje'] = 'ID de embargo inválido.';
    $_SESSION['Estilo'] = 'warning';
    header('Location: Embargos.php');
    exit;
}

$idEmbargo = intval($_GET['ID']);

// Intentar eliminar el embargo
if (Eliminar_Consulta($conexion, $idEmbargo)) {
    $_SESSION['Mensaje'] = 'Se ha eliminado el Embargo Judicial correctamente.';
    $_SESSION['Estilo'] = 'success';
} else {
    $_SESSION['Mensaje'] = 'No se logró eliminar el Embargo Judicial.';
    $_SESSION['Estilo'] = 'warning';
}

// Redirigir a la lista de embargos
header('Location: Embargos.php');
exit;
