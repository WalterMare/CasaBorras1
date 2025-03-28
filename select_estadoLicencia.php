<?php
require_once 'conexiondb.php';

function Listar_Estado_Licencia($conexion) {
    $sql = "SELECT idestadoLicencia, nombreEstado FROM estadolicencia";
    $resultado = mysqli_query($conexion, $sql);

    $estadosLicencia = [];
    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $estadosLicencia[] = $fila;
        }
    }

    return $estadosLicencia;
}
?>
