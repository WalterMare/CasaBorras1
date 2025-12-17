<?php
session_start();

require_once 'conexiondb.php';


if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No se especificó preliquidación";
    header("Location: Preliquidacion.php");
    exit;
}

$conn = ConexionBD();
$idPreliquidacion = intval($_GET['id']);

// 1. Verificar que esté en estado GENERADA (2)
$sql = "SELECT idEstadoPre FROM preliquidacion WHERE idpreliquidacion = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idPreliquidacion);
$stmt->execute();
$result = $stmt->get_result();
$estadoActual = $result->fetch_assoc()['idEstadoPre'] ?? null;

if ($estadoActual != 2) {
    $_SESSION['error'] = "Solo preliquidaciones GENERADAS pueden confirmarse";
    header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
    exit;
}

// 2. Actualizar estado a CONFIRMADA (3)
$sqlUpdate = "UPDATE preliquidacion SET idEstadoPre = 3 WHERE idpreliquidacion = ?";
$stmt = $conn->prepare($sqlUpdate);
$stmt->bind_param("i", $idPreliquidacion);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Preliquidación confirmada exitosamente";
} else {
    $_SESSION['error'] = "Error al confirmar: " . $conn->error;
}

header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
$conn->close();
?>