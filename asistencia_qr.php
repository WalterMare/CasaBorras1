<?php
session_start();

// Verificación si la sesión está vacía y redirigir al login si es necesario
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}

require_once 'conexiondb.php';

// Manejo de errores en la conexión a la base de datos
try {
  $conexion = ConexionBD();
} catch (Exception $e) {
  die('Error en la conexión: ' . $e->getMessage());
}

date_default_timezone_set('America/Argentina/Buenos_Aires');
include 'controlador_asistencia.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idEmpleado = intval($_POST['idEmpleado']);
    $accion = $_POST['accion']; // Entrada o Salida

    if ($accion === 'Entrada') {
        registrarEntrada($idEmpleado,$conexion);
    } elseif ($accion === 'Salida') {
        registrarSalida($idEmpleado,$conexion);
    }

    header("Location: asistencia_listado.php");
    exit;
}


