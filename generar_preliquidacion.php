<?php
session_start();
require_once 'conexiondb.php';
$conexion = ConexionBD();

if (!isset($_GET['id'])) {
    die("Error: ID no proporcionado.");
}

$idPreliquidacion = intval($_GET['id']);

// Validar permisos del usuario aquí (ej: $_SESSION['rol'])

// Cambiar estado a "Generada" (ID 2)
$consulta = "UPDATE preliquidacion SET idEstadoPre = 2 WHERE idpreliquidacion = ? AND idEstadoPre = 1";
$stmt = mysqli_prepare($conexion, $consulta);
mysqli_stmt_bind_param($stmt, "i", $idPreliquidacion);
mysqli_stmt_execute($stmt);

// Redirigir con mensaje de éxito
$_SESSION['mensaje'] = "Preliquidación generada correctamente";
header("Location: detalle_preliquidacion.php?id=" . $idPreliquidacion);
?>