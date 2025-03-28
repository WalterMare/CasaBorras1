<?php
require_once 'conexiondb.php';
header('Content-Type: application/json');

try {
    $conexion = ConexionBD();
    
    if (!isset($_GET['empleado_id'])) {
        throw new Exception("Parámetro empleado_id no proporcionado");
    }

    $empleadoId = filter_var($_GET['empleado_id'], FILTER_VALIDATE_INT);
    if ($empleadoId === false) {
        throw new Exception("ID de empleado inválido");
    }

    // Preparar la consulta
    $query = "SELECT fecha_inicio FROM empleado WHERE idempleado = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Vincular parámetros (nota: esto es diferente a PDO)
    $stmt->bind_param("i", $empleadoId);
    $stmt->execute();
    $stmt->bind_result($fecha_inicio);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => true,
            'fecha_inicio' => $fecha_inicio
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Empleado no encontrado'
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

