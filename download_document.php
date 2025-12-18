<?php
require_once 'conexiondb.php';
$conexion = ConexionBD();

if (!isset($_GET['file_id'])) {
    exit('Parámetro inválido');
}

$file_id = (int)$_GET['file_id'];

$query = "SELECT Documentacion FROM documento WHERE iddetalleLicencia = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $file_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $documento);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (empty($documento)) {
    exit('Documento no encontrado');
}

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=documento_licencia_$file_id.pdf");
header("Content-Length: " . strlen($documento));

echo $documento;
exit;
