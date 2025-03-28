<?php
function InsertarVacacion($conexion) {
    // Iniciamos transacción
    $conexion->begin_transaction();
    
    try {
        // 1. Insertar registro de vacaciones
        $queryVacacion = "INSERT INTO vacaciones 
                         (idempleado, fecha_inicio, fecha_fin, año, cantidad_dias, estado, vacaciones_restantes) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmtVacacion = $conexion->prepare($queryVacacion);
        $stmtVacacion->bind_param(
            "issiisi",
            $_POST['empleado'],
            $_POST['fecha_inicio'],
            $_POST['fecha_fin'],
            $_POST['año'],
            $_POST['cantidad_dias'],
            $_POST['estado'],
            $_POST['vacaciones_restantes']
        );
        $stmtVacacion->execute();
        
        // 2. Actualizar estado del empleado
        $queryEmpleado = "UPDATE empleado SET estado = 0 WHERE idempleado = ?";
        $stmtEmpleado = $conexion->prepare($queryEmpleado);
        $stmtEmpleado->bind_param("i", $_POST['empleado']);
        $stmtEmpleado->execute();
        
        // Si todo sale bien, confirmamos
        $conexion->commit();
        return true;
        
    } catch (Exception $e) {
        // Si algo falla, hacemos rollback
        $conexion->rollback();
        error_log("Error al registrar vacación: " . $e->getMessage());
        return false;
    }
}