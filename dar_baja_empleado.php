<?php
require_once 'conexiondb.php';

// Verificar si se recibió el ID del empleado
if (isset($_GET['ID'])) {
    $idEmpleado = intval($_GET['ID']);

    // Establecer conexión a la base de datos
    $conexion = ConexionBD();

    // Fecha actual para la baja
    $fechaBaja = date("Y-m-d");

    // Actualizar estado del empleado a 0 (inactivo) y establecer la fecha de baja
    $sql = "UPDATE empleado 
            SET estado = 0, fecha_baja = '$fechaBaja' 
            WHERE idempleado = $idEmpleado";

    if (mysqli_query($conexion, $sql)) {
        // Redirigir de vuelta al listado con un mensaje de éxito
        header("Location: listado_empleados.php?mensaje=Empleado dado de baja correctamente");
    } else {
        // Redirigir de vuelta al listado con un mensaje de error
        header("Location: listado_empleados.php?mensaje=Error al dar de baja al empleado");
    }

    // Cerrar conexión
    mysqli_close($conexion);
} else {
    // Si no se recibe un ID, redirigir al listado
    header("Location: listado_empleados.php?mensaje=ID de empleado no especificado");
}
?>
