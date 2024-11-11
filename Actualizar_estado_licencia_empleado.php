
<?php
function actualizarEstadoLicencia($conexion)
{
    $hoy = date('Y-m-d');

    // Consultamos todas las licencias cuya fecha de fin sea igual a la fecha actual
    $Licenciae = mysqli_query($conexion, "SELECT * FROM licencia WHERE fechafin = '$hoy'");

    // Verificamos si la consulta fue exitosa
    if (!$Licenciae) {
        return false; // En caso de error en la consulta
    }

    // Recorremos cada licencia obtenida
    while ($licencias = mysqli_fetch_assoc($Licenciae)) {
        $licencia_id = $licencias['idlicencia'];

        // Realizamos la actualización del estado de la licencia
        $SQL = "UPDATE licencia SET idEstado = 3 WHERE idlicencia = $licencia_id";
        $resultado = mysqli_query($conexion, $SQL);

        // Verificamos si la consulta de actualización fue exitosa
        if (!$resultado) {
            return false; // Si alguna consulta falla, retornamos false
        }
    }

    // Si todas las consultas fueron exitosas, retornamos true
    return true;
}
?>
