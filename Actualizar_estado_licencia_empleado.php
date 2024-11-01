
<?php
function actualizarEstadoLicencia($conexion)
{

    $hoy = date('Y-m-d');
    $Licenciae = mysqli_query($conexion, "SELECT * FROM licencia where fechafin= '$hoy' ");

    foreach ($Licenciae as $licencias) {
        $licencia_id = $licencias['idlicencia'];

        $SQL = mysqli_query($conexion, "UPDATE licencia AS l SET l.idEstado=3 WHERE l.idlicencia= $licencia_id ");
        if (!mysqli_query($conexion, $SQL)) {
            return false;
        }
        return true;
    }
}

?>