
<?php
function actualizarEstadoSancion($conexion)
{
    $hoy = date('Y-m-d');

    // Consulta para obtener las sanciones que vencen hoy y tienen ciertos tipos
    $query = "SELECT * FROM sancion WHERE fecha_fin = ? AND (IdTipoSancion = 3 OR IdTipoSancion = 4 OR IdTipoSancion = 5)";
    $stmt = mysqli_prepare($conexion, $query);
    
    // Enlazamos la fecha actual como parámetro
    mysqli_stmt_bind_param($stmt, 's', $hoy);
    
    // Ejecutamos la consulta
    mysqli_stmt_execute($stmt);
    
    // Obtenemos los resultados de la consulta
    $result = mysqli_stmt_get_result($stmt);

    // Verificamos si la consulta fue exitosa
    if (!$result) {
        return false;
    }

    // Recorremos cada sanción obtenida
    while ($suspension = mysqli_fetch_assoc($result)) {
        $sancion_id = $suspension['idsancion'];

        // Realizamos la actualización del estado de la sanción
        $updateQuery = "UPDATE sancion SET idEstadoSancion = 3 WHERE idsancion = ?";
        $updateStmt = mysqli_prepare($conexion, $updateQuery);

        // Enlazamos el ID de la sanción
        mysqli_stmt_bind_param($updateStmt, 'i', $sancion_id);

        // Ejecutamos la consulta de actualización
        $updateResult = mysqli_stmt_execute($updateStmt);

        // Si alguna actualización falla, retornamos false
        if (!$updateResult) {
            return false;
        }
    }

    // Si todas las actualizaciones fueron exitosas, retornamos true
    return true;
}
?>


<?php
function actualizarEstados($conexion)
{
    $hoy = date('Y-m-d');
    
    // Consulta para obtener las sanciones que vencen hoy
    $query = "SELECT * FROM sancion WHERE fecha_fin = '$hoy' AND (IdTipoSancion = 3 OR IdTipoSancion = 4 OR IdTipoSancion = 5)";
    $suspensiones = mysqli_query($conexion, $query);
    
    // Verificamos si la consulta fue exitosa
    if (!$suspensiones) {
        return false; // Si hubo un error en la consulta
    }

    // Recorremos todas las sanciones obtenidas
    while ($suspension = mysqli_fetch_assoc($suspensiones)) {
        $empleado_id = $suspension['idEmpleado'];

        // Actualizamos el estado del empleado
        $SQL = "UPDATE empleado SET estado = 1 WHERE idempleado = $empleado_id";
        $resultado = mysqli_query($conexion, $SQL);
        
        // Verificamos si la actualización fue exitosa
        if (!$resultado) {
            return false; // Si alguna actualización falla, devolvemos false
        }
    }

    // Si todas las actualizaciones fueron exitosas, actualizamos el estado de la sanción
    actualizarEstadoSancion($conexion);

    // Guardamos el mensaje y el estilo para la sesión
    $_SESSION['Mensaje'] = 'Los estados de los empleados se han actualizado correctamente.';
    $_SESSION['Estilo'] = 'success';

    return true;
}
?>











