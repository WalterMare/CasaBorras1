<?php
session_start();
require_once 'conexiondb.php';

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
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
    $_SESSION['error'] = "Solo preliquidaciones GENERADAS pueden revertirse";
    header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
    exit;
}

// 2. Eliminar detalles primero
$conn->begin_transaction();

try {
    $sqlDelete = "DELETE FROM detallepreliquidacion WHERE idPreliquidacion = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param("i", $idPreliquidacion);
    $stmt->execute();
    
    // 3. Cambiar estado a PENDIENTE (1)
    $sqlUpdate = "UPDATE preliquidacion SET idEstadoPre = 1 WHERE idpreliquidacion = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("i", $idPreliquidacion);
    $stmt->execute();
    
    $conn->commit();
    $_SESSION['mensaje'] = "Preliquidación revertida a pendiente";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error al revertir: " . $e->getMessage();
}

header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
$conn->close();
?>