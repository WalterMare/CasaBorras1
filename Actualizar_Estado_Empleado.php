<?php
function actualizarEstadoSancion($conexion)
{
    $hoy = date('Y-m-d');
    $ayer = date('Y-m-d');

    // ⚠️ 1. Sanciones pendientes que deben comenzar hoy o antes → pasar a EN CURSO
    $queryPendientes = "SELECT idsancion, idEmpleado, IdTipoSancion 
                        FROM sancion 
                        WHERE fecha_inicio <= ? AND idEstadoSancion = 1";
    $stmtPendientes = mysqli_prepare($conexion, $queryPendientes);
    mysqli_stmt_bind_param($stmtPendientes, 's', $hoy);
    mysqli_stmt_execute($stmtPendientes);
    $resultPendientes = mysqli_stmt_get_result($stmtPendientes);

    $tipos_que_inactivan = [3, 4, 5];

    while ($row = mysqli_fetch_assoc($resultPendientes)) {
        $idSancion = $row['idsancion'];
        $idEmpleado = $row['idEmpleado'];
        $tipo = (int)$row['IdTipoSancion'];

        // Actualizar sanción a estado "En curso" (2)
        $updateSancion = mysqli_prepare($conexion, "UPDATE sancion SET idEstadoSancion = 2 WHERE idsancion = ?");
        mysqli_stmt_bind_param($updateSancion, 'i', $idSancion);
        mysqli_stmt_execute($updateSancion);

        // Si el tipo requiere inactivar al empleado
        if (in_array($tipo, $tipos_que_inactivan)) {
            $updateEmpleado = mysqli_prepare($conexion, "UPDATE empleado SET estado = 0 WHERE idempleado = ?");
            mysqli_stmt_bind_param($updateEmpleado, 'i', $idEmpleado);
            mysqli_stmt_execute($updateEmpleado);
        }
    }

    // ✅ 2. Sanciones de tipo suspensión que terminaron ayer → pasar a FINALIZADA y activar empleado
    $queryFinalizadas = "SELECT idsancion, idEmpleado FROM sancion WHERE fecha_fin <= ? AND idEstadoSancion = 2";
    $stmtFinalizadas = mysqli_prepare($conexion, $queryFinalizadas);
    mysqli_stmt_bind_param($stmtFinalizadas, 's', $ayer);
    mysqli_stmt_execute($stmtFinalizadas);
    $resultFinalizadas = mysqli_stmt_get_result($stmtFinalizadas);

    while ($sancion = mysqli_fetch_assoc($resultFinalizadas)) {
        $sancion_id = $sancion['idsancion'];
        $empleado_id = $sancion['idEmpleado'];

        // Activar al empleado
        $stmtEmp = mysqli_prepare($conexion, "UPDATE empleado SET estado = 1 WHERE idempleado = ?");
        mysqli_stmt_bind_param($stmtEmp, 'i', $empleado_id);
        mysqli_stmt_execute($stmtEmp);

        // Marcar la sanción como finalizada
        $stmtSancion = mysqli_prepare($conexion, "UPDATE sancion SET idEstadoSancion = 3 WHERE idsancion = ?");
        mysqli_stmt_bind_param($stmtSancion, 'i', $sancion_id);
        mysqli_stmt_execute($stmtSancion);
    }

    //$_SESSION['Mensaje'] = 'Actualización automática de sanciones completada.';
   // $_SESSION['Estilo'] = 'success';
    return true;
}

?>










