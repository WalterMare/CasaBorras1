<?php
session_start();
require_once 'conexiondb.php';

// Validar sesión y permisos...
$conn = ConexionBD();
$idUsuario = $_SESSION['Usuario_Id'];

// 1. Crear preliquidación en estado PENDIENTE (ID=1)
$primerDiaMesAnterior = date('Y-m-01', strtotime('last month'));
$ultimoDiaMesAnterior = date('Y-m-t', strtotime('last month'));
$periodo = "$primerDiaMesAnterior a $ultimoDiaMesAnterior";

$sql = "INSERT INTO preliquidacion 
        (fecha, periodo, idEstadoPre, idUsuario) 
        VALUES (NOW(), '$periodo', 1, $idUsuario)";

if ($conn->query($sql) === TRUE) {
    $idPreliquidacion = $conn->insert_id;
    $_SESSION['mensaje'] = "Preliquidación creada (Pendiente). Ahora genera los cálculos.";
    header("Location: detalle_preliquidacion.php?id=$idPreliquidacion");
} else {
    $_SESSION['error'] = "Error al crear preliquidación: " . $conn->error;
    header("Location: Preliquidacion.php");
}
$conn->close();
?>