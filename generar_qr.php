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
require 'phpqrcode-master/qrlib.php';

$idEmpleado = intval($_GET['idEmpleado']);

// Obtener información del empleado
$consulta = "SELECT nombre, apellido FROM empleado WHERE idempleado = $idEmpleado";
$resultado = mysqli_query($conexion, $consulta);
$empleado = mysqli_fetch_assoc($resultado);

if ($empleado) {
    $datosQR = "empleado:$idEmpleado";
    $nombreArchivo = "qr_empleado_$idEmpleado.png";
    QRcode::png($datosQR, $nombreArchivo, QR_ECLEVEL_L, 5);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar QR</title>
</head>
<body>
    <h2>QR para <?= $empleado['nombre'] . " " . $empleado['apellido'] ?></h2>
    <img src="<?= $nombreArchivo ?>" alt="QR de asistencia">
    <br><br>
    <a href="Asistencia_Empleados.php">Volver</a>
</body>
</html>
