<?php
function actualizarEstadoLicencia($conexion)
{
    // Obtenemos la fecha actual
    $hoy = date('Y-m-d'); // o usar CURDATE() en SQL

    // Consultamos todas las licencias cuya fecha de fin sea igual o menor a la fecha actual
    $query = "SELECT idlicencia, idEmpleado FROM licencia WHERE fechafin <= ?";
    $stmt = mysqli_prepare($conexion, $query);

    if (!$stmt) {
        error_log("Error preparando la consulta: " . mysqli_error($conexion));
        return false;
    }

    // Enlazamos el parámetro para la fecha
    mysqli_stmt_bind_param($stmt, "s", $hoy);

    // Ejecutamos la consulta
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error ejecutando la consulta para obtener licencias: " . mysqli_error($conexion));
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    if (!$resultado) {
        error_log("Error obteniendo el resultado: " . mysqli_error($conexion));
        return false;
    }

    // Procesamos las licencias vencidas
    while ($licencia = mysqli_fetch_assoc($resultado)) {
        $licencia_id = $licencia['idlicencia'];
        $empleado_id = $licencia['idEmpleado'];

        // Actualizar el estado de la licencia a 3 (finalizada)
        $SQL_actualizar_licencia = "UPDATE licencia SET IdEstado = 3 WHERE idlicencia = ?";
        $stmt_actualizar_licencia = mysqli_prepare($conexion, $SQL_actualizar_licencia);

        if (!$stmt_actualizar_licencia) {
            error_log("Error preparando la actualización de licencia: " . mysqli_error($conexion));
            return false;
        }

        mysqli_stmt_bind_param($stmt_actualizar_licencia, "i", $licencia_id);
        if (!mysqli_stmt_execute($stmt_actualizar_licencia)) {
            error_log("Error actualizando la licencia con id: $licencia_id - " . mysqli_error($conexion));
            return false;
        } else {
            error_log("Licencia con id: $licencia_id actualizada a estado 3.");
        }

        // Actualizar el estado del empleado a 1 (activo)
        $SQL_actualizar_empleado = "UPDATE empleado SET estado = 1 WHERE idempleado = ?";
        $stmt_actualizar_empleado = mysqli_prepare($conexion, $SQL_actualizar_empleado);

        if (!$stmt_actualizar_empleado) {
            error_log("Error preparando la actualización de empleado: " . mysqli_error($conexion));
            return false;
        }

        mysqli_stmt_bind_param($stmt_actualizar_empleado, "i", $empleado_id);
        if (!mysqli_stmt_execute($stmt_actualizar_empleado)) {
            error_log("Error actualizando el empleado con id: $empleado_id - " . mysqli_error($conexion));
            return false;
        } else {
            error_log("Empleado con id: $empleado_id actualizado a estado 1 (activo).");
        }
    }

    // Si se procesaron correctamente todas las licencias, retornamos true
    error_log("Proceso completado. Estados actualizados para licencias y empleados.");
    return true;
}
