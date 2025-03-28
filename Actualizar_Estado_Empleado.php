<?php
function actualizarEstadoSancion($conexion)
{
    // Obtener la fecha de ayer (un día antes de hoy)
    $ayer = date('Y-m-d', strtotime('-1 day'));

    // Consulta para obtener las sanciones que vencieron ayer y son de tipo suspensión (IdTipoSancion = 3)
    $query = "SELECT idsancion, idEmpleado FROM sancion WHERE fecha_fin = ? AND IdTipoSancion = 3";
    $stmt = mysqli_prepare($conexion, $query);

    // Verificar si la preparación de la consulta fue exitosa
    if (!$stmt) {
        $_SESSION['Mensaje'] = 'Error al preparar la consulta: ' . mysqli_error($conexion);
        $_SESSION['Estilo'] = 'danger';
        return false;
    }

    // Enlazar la fecha de ayer como parámetro
    mysqli_stmt_bind_param($stmt, 's', $ayer);

    // Ejecutar la consulta
    mysqli_stmt_execute($stmt);

    // Obtener los resultados de la consulta
    $result = mysqli_stmt_get_result($stmt);

    // Verificar si la consulta fue exitosa
    if (!$result) {
        $_SESSION['Mensaje'] = 'Error al obtener las sanciones: ' . mysqli_error($conexion);
        $_SESSION['Estilo'] = 'danger';
        return false;
    }

    // Recorrer cada sanción obtenida
    while ($sancion = mysqli_fetch_assoc($result)) {
        $sancion_id = $sancion['idsancion'];
        $empleado_id = $sancion['idEmpleado'];

        // Actualizar el estado del empleado a 1 (activo)
        $updateEmpleadoQuery = "UPDATE empleado SET estado = 1 WHERE idempleado = ?";
        $updateEmpleadoStmt = mysqli_prepare($conexion, $updateEmpleadoQuery);

        // Verificar si la preparación de la consulta fue exitosa
        if (!$updateEmpleadoStmt) {
            $_SESSION['Mensaje'] = 'Error al preparar la consulta de actualización del empleado: ' . mysqli_error($conexion);
            $_SESSION['Estilo'] = 'danger';
            return false;
        }

        // Enlazar el ID del empleado
        mysqli_stmt_bind_param($updateEmpleadoStmt, 'i', $empleado_id);

        // Ejecutar la consulta de actualización del empleado
        $updateEmpleadoResult = mysqli_stmt_execute($updateEmpleadoStmt);

        // Si la actualización del empleado falla, retornar false
        if (!$updateEmpleadoResult) {
            $_SESSION['Mensaje'] = 'Error al actualizar el estado del empleado: ' . mysqli_error($conexion);
            $_SESSION['Estilo'] = 'danger';
            return false;
        }

        // Actualizar el estado de la sanción a 3 (finalizada)
        $updateSancionQuery = "UPDATE sancion SET idEstadoSancion = 3 WHERE idsancion = ?";
        $updateSancionStmt = mysqli_prepare($conexion, $updateSancionQuery);

        // Verificar si la preparación de la consulta fue exitosa
        if (!$updateSancionStmt) {
            $_SESSION['Mensaje'] = 'Error al preparar la consulta de actualización de la sanción: ' . mysqli_error($conexion);
            $_SESSION['Estilo'] = 'danger';
            return false;
        }

        // Enlazar el ID de la sanción
        mysqli_stmt_bind_param($updateSancionStmt, 'i', $sancion_id);

        // Ejecutar la consulta de actualización de la sanción
        $updateSancionResult = mysqli_stmt_execute($updateSancionStmt);

        // Si la actualización de la sanción falla, retornar false
        if (!$updateSancionResult) {
            $_SESSION['Mensaje'] = 'Error al actualizar el estado de la sanción: ' . mysqli_error($conexion);
            $_SESSION['Estilo'] = 'danger';
            return false;
        }
    }

    // Si todas las actualizaciones fueron exitosas, retornar true
    $_SESSION['Mensaje'] = 'Los estados de los empleados y sanciones se han actualizado correctamente.';
    $_SESSION['Estilo'] = 'success';
    return true;
}
?>










