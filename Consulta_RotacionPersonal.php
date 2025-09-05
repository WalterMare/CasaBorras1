<?php
function obtenerRotacionPersonal($conexion, $fechaInicio, $fechaFin) {
$altas=0;
$bajas=0;
$plantillaInicial=0;
$plantillaFinal=0;

    // Inicializar resultado
    $resultado = [
        'Altas' => 0,
        'Bajas' => 0,
        'PlantillaInicial' => 0,
        'PlantillaFinal' => 0,
        'PlantillaPromedio' => 0,
        'RotacionPorcentaje' => 0
    ];

    // Altas
    $sqlAltas = "SELECT COUNT(*) AS Altas
                 FROM empleado
                 WHERE fecha_inicio BETWEEN ? AND ?";
    $stmt = $conexion->prepare($sqlAltas);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $stmt->bind_result($altas);
    $stmt->fetch();
    $resultado['Altas'] = $altas;
    $stmt->close();

    // Bajas
    $sqlBajas = "SELECT COUNT(*) AS Bajas
                 FROM empleado
                 WHERE fecha_baja BETWEEN ? AND ?
                   AND estado = 0";
    $stmt = $conexion->prepare($sqlBajas);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $stmt->bind_result($bajas);
    $stmt->fetch();
    $resultado['Bajas'] = $bajas;
    $stmt->close();

    // Plantilla Inicial
    $sqlPlantillaIni = "SELECT COUNT(*) AS PlantillaInicial
                        FROM empleado
                        WHERE fecha_inicio <= ?
                          AND (fecha_baja IS NULL OR fecha_baja > ?)";
    $stmt = $conexion->prepare($sqlPlantillaIni);
    $stmt->bind_param("ss", $fechaInicio, $fechaInicio);
    $stmt->execute();
    $stmt->bind_result($plantillaInicial);
    $stmt->fetch();
    $resultado['PlantillaInicial'] = $plantillaInicial;
    $stmt->close();

    // Plantilla Final
    $sqlPlantillaFin = "SELECT COUNT(*) AS PlantillaFinal
                        FROM empleado
                        WHERE fecha_inicio <= ?
                          AND (fecha_baja IS NULL OR fecha_baja > ?)";
    $stmt = $conexion->prepare($sqlPlantillaFin);
    $stmt->bind_param("ss", $fechaFin, $fechaFin);
    $stmt->execute();
    $stmt->bind_result($plantillaFinal);
    $stmt->fetch();
    $resultado['PlantillaFinal'] = $plantillaFinal;
    $stmt->close();

    // Plantilla Promedio
    $resultado['PlantillaPromedio'] = ($plantillaInicial + $plantillaFinal) / 2;

    // Rotación (%)
    if ($resultado['PlantillaPromedio'] > 0) {
        $resultado['RotacionPorcentaje'] = ($bajas / $resultado['PlantillaPromedio']) * 100;
    }

    return $resultado;
}
?>