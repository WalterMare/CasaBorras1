<?php
function ActualizarEstadoEmbargos($vConexion)
{
    // Fecha actual
    $hoy = date('Y-m-d');

    // 1. Inactivar embargos vencidos (fecha_fin < hoy)
    $sql_vencidos = "UPDATE embargo 
                      SET estado = 0 
                      WHERE fecha_fin IS NOT NULL 
                        AND fecha_fin < ?";
    if ($stmt = mysqli_prepare($vConexion, $sql_vencidos)) {
        mysqli_stmt_bind_param($stmt, "s", $hoy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 2. Inactivar embargos que aún no iniciaron (fecha_inicio > hoy)
    $sql_no_iniciados = "UPDATE embargo 
                         SET estado = 0 
                         WHERE fecha_inicio > ?";
    if ($stmt = mysqli_prepare($vConexion, $sql_no_iniciados)) {
        mysqli_stmt_bind_param($stmt, "s", $hoy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 3. Activar embargos que están vigentes (fecha_inicio <= hoy y fecha_fin >= hoy o fecha_fin IS NULL)
    $sql_activos = "UPDATE embargo 
                    SET estado = 1 
                    WHERE fecha_inicio <= ? 
                      AND (fecha_fin >= ? OR fecha_fin IS NULL)";
    if ($stmt = mysqli_prepare($vConexion, $sql_activos)) {
        mysqli_stmt_bind_param($stmt, "ss", $hoy, $hoy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
