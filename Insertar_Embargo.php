<?php
function InsertarEmbargo($vConexion)
{
    // 1. Obtenemos el sueldo del empleado según su cargo
    $idEmpleado = $_POST['empleado'];
    $sqlEmpleado = "
        SELECT c.sueldo_basico 
        FROM empleado e
        JOIN cargo c ON e.Idcargo = c.idcargo
        WHERE e.idempleado = ?
    ";
    $stmtEmp = mysqli_prepare($vConexion, $sqlEmpleado);
    mysqli_stmt_bind_param($stmtEmp, "i", $idEmpleado);
    mysqli_stmt_execute($stmtEmp);
    mysqli_stmt_bind_result($stmtEmp, $sueldoBasico);
    mysqli_stmt_fetch($stmtEmp);
    mysqli_stmt_close($stmtEmp);

    // 2. Preparamos las variables del formulario
    $expediente   = $_POST['expediente'];
    $tipo         = $_POST['tipo'];
    $fecha        = $_POST['fecha'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
    $estado       = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;
    $monto        = !empty($_POST['monto']) ? $_POST['monto'] : null;
    $porcentaje   = !empty($_POST['porcentaje']) ? $_POST['porcentaje'] : null;
    $descripcion  = $_POST['descripcion'];

    // 3. Calculamos monto o porcentaje si falta
    if (empty($monto) && !empty($porcentaje)) {
        $monto = $sueldoBasico * $porcentaje / 100;
    } elseif (empty($porcentaje) && !empty($monto)) {
        $porcentaje = ($monto / $sueldoBasico) * 100;
    }

    // 4. Preparamos el INSERT
    $sql = "INSERT INTO embargo 
        (expediente, tipo, fecha, fecha_inicio, fecha_fin, estado, monto, porcentaje, idEmpleado, descripcion) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($vConexion, $sql)) {

        // Vinculamos los parámetros (s=string, i=int, d=double)
        mysqli_stmt_bind_param(
            $stmt,
            "sssssiddis",
            $expediente,
            $tipo,
            $fecha,
            $fecha_inicio,
            $fecha_fin,
            $estado,
            $monto,
            $porcentaje,
            $idEmpleado,
            $descripcion
        );

        // Ejecutamos
        if (!mysqli_stmt_execute($stmt)) {
            die('<h4>Error al insertar el embargo: ' . mysqli_stmt_error($stmt) . '</h4>');
        }

        mysqli_stmt_close($stmt);
        return true;
    } else {
        die('<h4>Error en la preparación del INSERT: ' . mysqli_error($vConexion) . '</h4>');
    }
}
