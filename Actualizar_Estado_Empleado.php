
<?php
function actualizarEstadoSancion($conexion)
{

    $hoy = date('Y-m-d');
    $sancionE = mysqli_query($conexion, "SELECT * FROM sancion where fecha_fin= '$hoy' and IdTipoSancion=3 OR IdTipoSancion=4 or IdTipoSancion=5");

    foreach ($sancionE as $suspension) {
        $sancion_id = $suspension['idsancion'];

        $SQL = mysqli_query($conexion, 'UPDATE sancion AS s SET s.idEstadoSancion=3 WHERE s.idsancion=$sancion_id');
        if (!mysqli_query($conexion, $SQL)) {
            return false;
        }
        return true;
    }
}
?>

<?php
function actualizarEstados($conexion)
{

    $hoy = date('Y-m-d');
    $suspensiones = mysqli_query($conexion, "SELECT * FROM sancion where fecha_fin= '$hoy' and IdTipoSancion=3 OR IdTipoSancion=4 or IdTipoSancion=5");

    foreach ($suspensiones as $suspension) {
        $empleado_id = $suspension['idEmpleado'];

        $SQL = mysqli_query($conexion, 'UPDATE empleado AS E SET E.estado=1 WHERE E.idempleado=$empleado_id');
        if (!mysqli_query($conexion, $SQL)) {
            return false;
        } else {
            $_SESSION['Mensaje'] .= '';
            $_SESSION['Estilo'] = 'success';
            actualizarEstadoSancion($conexion);
        }
        return true;
    }
}
?>










