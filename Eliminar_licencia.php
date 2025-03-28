<?php
session_start();
require_once 'conexiondb.php';

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

$conexion = ConexionBD();
$idLicencia = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idLicencia == 0) {
    echo "ID de licencia inválido.";
    exit;
}

// Obtener ID del empleado antes de eliminar la licencia
$sqlEmpleado = "SELECT idEmpleado FROM licencia WHERE idlicencia = ?";
$stmtEmp = mysqli_prepare($conexion, $sqlEmpleado);
mysqli_stmt_bind_param($stmtEmp, "i", $idLicencia);
mysqli_stmt_execute($stmtEmp);
$resultadoEmp = mysqli_stmt_get_result($stmtEmp);
$empleado = mysqli_fetch_assoc($resultadoEmp);
$idEmpleado = $empleado['idEmpleado'] ?? 0;

// Eliminar primero los detalles y documentos relacionados
$sqlDeleteDetalle = "DELETE FROM detallelicencia WHERE idLicencia = ?";
$stmtDetalle = mysqli_prepare($conexion, $sqlDeleteDetalle);
mysqli_stmt_bind_param($stmtDetalle, "i", $idLicencia);
mysqli_stmt_execute($stmtDetalle);

$sqlDeleteDocumento = "DELETE FROM documento WHERE iddetalleLicencia IN (SELECT iddetalleLicencia FROM detallelicencia WHERE idLicencia = ?)";
$stmtDoc = mysqli_prepare($conexion, $sqlDeleteDocumento);
mysqli_stmt_bind_param($stmtDoc, "i", $idLicencia);
mysqli_stmt_execute($stmtDoc);

// Eliminar la licencia
$sqlDeleteLicencia = "DELETE FROM licencia WHERE idlicencia = ?";
$stmtLic = mysqli_prepare($conexion, $sqlDeleteLicencia);
mysqli_stmt_bind_param($stmtLic, "i", $idLicencia);
if (mysqli_stmt_execute($stmtLic)) {
    header("Location: Licencias.php");
    exit;
} else {
    echo "Error al eliminar la licencia.";
}
?>
