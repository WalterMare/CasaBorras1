<?php
function actualizarEstadosVacacionales($conexion)
{
    try {
        $fechaHoy = date('Y-m-d');

        // 1. Reactivar empleados que ya terminaron vacaciones aprobadas
        $reactivarQuery = "
            UPDATE empleado e
            JOIN vacaciones v ON e.idempleado = v.idempleado
            SET e.estado = 1
            WHERE v.fecha_fin < ? 
              AND e.estado = 0 
              AND e.fecha_baja IS NULL
              AND v.estado = 'Aprobado'
        ";
        $reactivarStmt = $conexion->prepare($reactivarQuery);
        $reactivarStmt->bind_param("s", $fechaHoy);
        $reactivarStmt->execute();
        $reactivados = $reactivarStmt->affected_rows;

        // 2. Desactivar empleados que están actualmente de vacaciones aprobadas
        $desactivarQuery = "
            UPDATE empleado e
            JOIN vacaciones v ON e.idempleado = v.idempleado
            SET e.estado = 0
            WHERE v.fecha_inicio <= ? 
              AND v.fecha_fin >= ? 
              AND e.estado = 1 
              AND e.fecha_baja IS NULL
              AND v.estado = 'Aprobado'
        ";
        $desactivarStmt = $conexion->prepare($desactivarQuery);
        $desactivarStmt->bind_param("ss", $fechaHoy, $fechaHoy);
        $desactivarStmt->execute();
        $desactivados = $desactivarStmt->affected_rows;

        // 3. Log opcional
        error_log("Estados vacacionales actualizados: $reactivados reactivados, $desactivados desactivados");

        return [
            'reactivados' => $reactivados,
            'desactivados' => $desactivados
        ];
    } catch (Exception $e) {
        error_log("Error al actualizar estados vacacionales: " . $e->getMessage());
        return false;
    }
}

// Ejemplo de uso con MySQLi:
// $conexion = new mysqli("localhost", "usuario", "contraseña", "basedatos");
// $resultado = actualizarEstadosVacacionales($conexion);
