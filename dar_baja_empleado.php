<?php
require_once 'conexiondb.php';

if (isset($_POST['id_empleado'], $_POST['motivo_baja'])) {
    $idEmpleado = intval($_POST['id_empleado']);
    $motivo = trim($_POST['motivo_baja']);
    $fechaBaja = date("Y-m-d");

    $conexion = ConexionBD();

    $stmt = mysqli_prepare($conexion, "UPDATE empleado SET estado = 0, fecha_baja = ?, motivo_baja = ? WHERE idempleado = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $fechaBaja, $motivo, $idEmpleado);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: listado_empleados.php?mensaje=Empleado dado de baja correctamente");
            exit;
        }
    }

    header("Location: listado_empleados.php?mensaje=Error al dar de baja al empleado");
    mysqli_close($conexion);
} else {
    header("Location: listado_empleados.php?mensaje=Datos incompletos para dar de baja");
}
?>

