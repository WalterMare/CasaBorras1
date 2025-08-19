<?php

require_once 'conexiondb.php';
$conexion = ConexionBD();

$empleado_id = $_GET['empleado_id'] ?? null;

if (!$empleado_id) {
    echo 0;
    exit;
}

function calcularDiasAntiguedad($conexion, $idEmpleado) {
    $stmt = $conexion->prepare("SELECT fecha_inicio FROM empleado WHERE idempleado = ?");
    $stmt->bind_param("i", $idEmpleado);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) return 0;

    $fechaIngreso = $row['fecha_inicio'];
    $fechaActual = new DateTime();
    $fechaIngresoObj = new DateTime($fechaIngreso);

    $mesesTrabajados = ($fechaActual->format('Y') - $fechaIngresoObj->format('Y')) * 12;
    $mesesTrabajados += $fechaActual->format('m') - $fechaIngresoObj->format('m');
    if ($fechaActual->format('d') < $fechaIngresoObj->format('d')) $mesesTrabajados--;

    if ($mesesTrabajados < 6) return 0;
    if ($mesesTrabajados <= 12) return $mesesTrabajados;

    $antiguedad = floor($mesesTrabajados / 12);
    if ($antiguedad < 5) return 14;
    if ($antiguedad < 10) return 21;
    if ($antiguedad < 20) return 28;
    return 35;
}

// Días totales por antigüedad
$diasTotales = calcularDiasAntiguedad($conexion, $empleado_id);

$stmt = $conexion->prepare("
    SELECT SUM(cantidad_dias) AS diasTomados 
    FROM vacaciones 
    WHERE idempleado = ? AND estado = 'Aprobado'
");
$stmt->bind_param("i", $empleado_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$diasTomados = $row['diasTomados'] ?? 0;

// Calcular días restantes
$diasRestantes = $diasTotales - $diasTomados;
echo $diasRestantes >= 0 ? $diasRestantes : 0;
