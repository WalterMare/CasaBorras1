<?php
require_once 'conexiondb.php';
$conexion = ConexionBD();

if (isset($_GET['empleado_id'])) {
    $empleadoId = $_GET['empleado_id'];

    try {
        // Preparar la consulta SQL
        $query = "SELECT fecha_inicio FROM empleado WHERE idempleado = ?";
        $stmt = $conexion->prepare($query);
        $stmt->execute([$empleadoId]);

        // Obtener el resultado de la consulta
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si se obtuvo la fecha de inicio
        if ($result) {
            echo $result['fecha_inicio']; // Retornamos la fecha de ingreso
        } else {
            echo "Empleado no encontrado"; // Mensaje en caso de no encontrar el empleado
        }
    } catch (PDOException $e) {
        // Manejo de excepciones si ocurre algún error en la consulta
        echo "Error en la consulta: " . $e->getMessage();
    }
}
?>

