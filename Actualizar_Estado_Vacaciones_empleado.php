<?php
function actualizarEstadoEmpleadosEnVacaciones() {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');

    // Obtener la fecha actual
    $fechaHoy = date('Y-m-d');

    // Verificar los empleados que están de vacaciones y actualizar su estado
    $stmt = $pdo->prepare("SELECT idempleado, fecha_inicio, fecha_fin FROM vacaciones WHERE fecha_inicio <= ? AND fecha_fin >= ?");
    $stmt->execute([$fechaHoy, $fechaHoy]);

    $empleadosEnVacaciones = $stmt->fetchAll();

    foreach ($empleadosEnVacaciones as $empleado) {
        $idEmpleado = $empleado['idempleado'];

        // Actualizar el estado del empleado a "Inactivo" (vacaciones)
        $updateStmt = $pdo->prepare("UPDATE empleados SET estado = 0 WHERE idempleado = ?");
        $updateStmt->execute([$idEmpleado]);

        echo "Empleado con ID $idEmpleado actualizado a Inactivo debido a vacaciones.\n";
    }
}

?>
