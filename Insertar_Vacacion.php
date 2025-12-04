<?php
function obtenerDiasRestantes($conexion, $idEmpleado, $anio)
{
    $sql = "SELECT vacaciones_restantes
            FROM vacaciones
            WHERE idempleado = ? AND anio = ? 
            ORDER BY idvacaciones DESC
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $idEmpleado, $anio);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        return (int)$fila['vacaciones_restantes'];
    }

    return null; // No tiene registros todavía
}

function InsertarVacacion($conexion)
{
    $idEmpleado = (int)($_POST['empleadoid'] ?? 0);
    $fechaInicio = $_POST['fecha_inicio'] ?? null;
    $fechaFin = $_POST['fecha_fin'] ?? null;
    $cantidadDias = (int)($_POST['cantidad_dias'] ?? 0);
    $observaciones = $_POST['observaciones'] ?? '';

    if (!$idEmpleado || !$fechaInicio || !$fechaFin || $cantidadDias <= 0) {
        die("<h4>Error: Complete todos los campos obligatorios.</h4>");
    }

    // Año de la solicitud
    $anio = (int)date('Y', strtotime($fechaInicio));

    // 1. Días según antigüedad (tope)
    $diasAntiguedad = (int)($_POST['dias_antiguedad'] ?? 0);

    // 2. Obtener el último saldo real guardado
    $saldoActual = obtenerDiasRestantes($conexion, $idEmpleado, $anio);

    // Si no hay saldo, usar antigüedad como saldo inicial
    if ($saldoActual === null) {
        $saldoActual = $diasAntiguedad;
    }

    // 3. Calcular nuevo saldo
    $resto = $saldoActual - $cantidadDias;

    if ($resto < 0) {
        return "Error: El empleado solo tiene $saldoActual días disponibles.";
    }

    $estado = "Pendiente";
    $fechaRegistro = date('Y-m-d H:i:s');

    $sql = "INSERT INTO vacaciones 
            (idempleado, fecha_inicio, fecha_fin, cantidad_dias, estado, anio, vacaciones_restantes, observaciones, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
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
