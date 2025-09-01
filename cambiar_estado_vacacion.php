<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['Usuario_Nombre'])) {
    header("Location: cerrarsesion.php");
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// -------------------------------------------
// Definición de la función antes de usarla
// -------------------------------------------
function actualizarEstadoEmpleadoPorVacacion($conexion, $idvacaciones) {
    // Obtener la vacación
    $stmt = $conexion->prepare("SELECT idempleado, fecha_inicio, fecha_fin FROM vacaciones WHERE idvacaciones = ? AND estado = 'Aprobado'");
    $stmt->bind_param("i", $idvacaciones);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if (!$resultado) return false;

    $idEmpleado = $resultado['idempleado'];
    $fechaHoy = date('Y-m-d');

    // Si la vacación ya empieza o está vigente, desactivar al empleado
    if ($fechaHoy >= $resultado['fecha_inicio'] && $fechaHoy <= $resultado['fecha_fin']) {
        $stmt2 = $conexion->prepare("UPDATE empleado SET estado = 0 WHERE idempleado = ?");
        $stmt2->bind_param("i", $idEmpleado);
        $stmt2->execute();
        $stmt2->close();
    }

    return true;
}

// -------------------------------------------
// Código para cambiar el estado de la vacación
// -------------------------------------------
$idvacaciones = $_GET['idvacaciones'] ?? null;
$nuevo_estado = $_GET['nuevo_estado'] ?? null;

$estados_validos = ['Pendiente', 'Aprobado', 'Rechazado'];

if ($idvacaciones && in_array($nuevo_estado, $estados_validos)) {
    $stmt = $conexion->prepare("UPDATE vacaciones SET estado = ? WHERE idvacaciones = ?");
    $stmt->bind_param("si", $nuevo_estado, $idvacaciones);

    if ($stmt->execute()) {
        // Si se aprueba, desactivar al empleado
        if ($nuevo_estado === 'Aprobado') {
            actualizarEstadoEmpleadoPorVacacion($conexion, $idvacaciones);
        }

        $respuesta = ['success' => true, 'nuevo_estado' => $nuevo_estado];
    } else {
        $respuesta = ['success' => false, 'mensaje' => 'Error al actualizar el estado.'];
    }
    $stmt->close();
} else {
    $respuesta = ['success' => false, 'mensaje' => 'Datos inválidos.'];
}

// Devolver JSON para AJAX
header('Content-Type: application/json');
echo json_encode($respuesta);
exit;
?>

