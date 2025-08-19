<?php
function InsertarVacacion($conexion)
{
    // 1. Obtener datos del POST
    $idEmpleado = (int)($_POST['empleadoid'] ?? 0);
    $fechaInicio = $_POST['fecha_inicio'] ?? null;
    $fechaFin = $_POST['fecha_fin'] ?? null;
    $cantidadDias = (int)($_POST['cantidad_dias'] ?? 0);
    $observaciones = $_POST['observaciones'] ?? '';

    if (!$idEmpleado || !$fechaInicio || !$fechaFin || $cantidadDias <= 0) {
        die("<h4>Error: Complete todos los campos obligatorios.</h4>");
    }

    $diasAntiguedad = $_POST['dias_antiguedad'] ?? '';

    $resto = $diasAntiguedad - $cantidadDias;
    if ($resto < 0) {
        return "El empleado no tiene suficientes días disponibles.";
    }

    // 4. Año de la solicitud
    $anio = date('Y', strtotime($fechaInicio));
    $fechaRegistro = date('Y-m-d H:i:s');

    // 5. Estado inicial
    $estado = 'Pendiente';

    // 5. Insertar con sentencia preparada
    $sql = "INSERT INTO vacaciones 
        (idempleado, fecha_inicio, fecha_fin, cantidad_dias, estado, anio, vacaciones_restantes, observaciones,fecha_registro) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return "Error en la preparación de la consulta: " . $conexion->error;
    }

    $stmt->bind_param(
        "issisisss",
        $idEmpleado,
        $fechaInicio,
        $fechaFin,
        $cantidadDias,
        $estado,
        $anio,
        $resto,
        $observaciones,
        $fechaRegistro
    );

    if (!$stmt->execute()) {
        return "Error al guardar las vacaciones: " . $stmt->error;
    }

    return true;
}
