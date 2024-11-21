<?php
function actualizarEstadoLicencia($conexion)
{
    // Obtenemos la fecha actual
    $hoy = date('Y-m-d');  // o usar 'CURDATE()' directamente en SQL

    // Consultamos todas las licencias cuya fecha de fin sea igual a la fecha actual
    $query = "SELECT * FROM licencia WHERE fechafin <= ?";
    $stmt = mysqli_prepare($conexion, $query);

    // Enlazamos el parámetro para la fecha
    mysqli_stmt_bind_param($stmt, "s", $hoy);

    // Ejecutamos la consulta
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    // Verificamos si la consulta fue exitosa
    if (!$resultado) {
        error_log("Error al ejecutar la consulta para obtener las licencias: " . mysqli_error($conexion));
        return false;
    }

    // Verificamos si se obtuvieron licencias
    $licencia_encontrada = false;
    while ($licencias = mysqli_fetch_assoc($resultado)) {
        $licencia_encontrada = true;
        $licencia_id = $licencias['idlicencia'];

        // Registramos el id de la licencia que se va a actualizar
        error_log("Actualizando licencia con id: " . $licencia_id);

        // Realizamos la actualización del estado de la licencia
        $SQL = "UPDATE licencia SET IdEstado = 3 WHERE idlicencia = ?";
        $stmt_update = mysqli_prepare($conexion, $SQL);

        // Enlazamos el parámetro de idlicencia
        mysqli_stmt_bind_param($stmt_update, "i", $licencia_id);

        // Ejecutamos la consulta de actualización
        $resultado_update = mysqli_stmt_execute($stmt_update);

        // Verificamos si la consulta de actualización fue exitosa
        if (!$resultado_update) {
            // Registrar el error si no se pudo actualizar
            error_log("Error al actualizar la licencia con id: " . $licencia_id . " - " . mysqli_error($conexion));
            return false;
        }
    }

    if (!$licencia_encontrada) {
        error_log("No se encontraron licencias con fecha de fin igual a hoy: " . $hoy);
    }

    // Si todas las consultas fueron exitosas, retornamos true
    return true;
}
?>


