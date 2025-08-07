<?php

function Listar_Viaticos_Empleado($vConexion, $idEmpleado, $idPreliquidacion)
{
    $Listado = [];

    $idEmpleado = (int)$idEmpleado;
    $idPreliquidacion = (int)$idPreliquidacion;

    // 1. Obtener período de preliquidación
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($vConexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $resultadoPeriodo = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($resultadoPeriodo);

    if (!$filaPeriodo) {
        return $Listado;
    }

    // 2. Parsear fechas
    $partes = explode(' a ', $filaPeriodo['periodo']);
    if (count($partes) !== 2) {
        return $Listado;
    }

    $fechaInicio = $partes[0];
    $fechaFin = $partes[1];

    // 3. Consulta de viáticos filtrados por período
    $consulta = "
        SELECT 
            v.idviatico,
            t.descripcion AS tipo_viatico,
            v.fechaotorgamiento,
            v.monto
        FROM viatico v
        JOIN tipo_viatico t ON v.idTipo = t.idTipo_viatico
        WHERE v.idEmpleado = ?
          AND v.fechaotorgamiento BETWEEN ? AND ?
        ORDER BY v.fechaotorgamiento
    ";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "iss", $idEmpleado, $fechaInicio, $fechaFin);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[] = [
            'ID' => $data['idviatico'],
            'TIPO' => $data['tipo_viatico'],
            'FECHA' => $data['fechaotorgamiento'],
            'MONTO' => $data['monto'],
        ];
    }

    return $Listado;
}
