<?php
function InsertarSancion($vConexion)
{
    // Obtener la fecha de inicio y la cantidad de días
    $fechainicio = $_POST['fecha'];
    $dias = $_POST['dias'];

    // Calcular la fecha final de la sanción
    $fechainicial = new DateTime($fechainicio);
    $fecha_final = $fechainicial->modify("+$dias days");
    $fecha_finalicima = $fecha_final->format('Y-m-d');

    // Insertar la sanción en la base de datos
    $SQL_Insert = "INSERT INTO sancion (idsancion, fecha_inicio, IdTipoSancion, descripcion, idEmpleado, cantidadDias, idEstadoSancion, fecha_fin) 
                   VALUES (null, '" . $_POST['fecha'] . "', '" . $_POST['tipo'] . "', '" . $_POST['descripcion'] . "', '" . $_POST['empleado'] . "', '" . $_POST['dias'] . "', '" . $_POST['estado'] . "', '" . $fecha_finalicima . "')";

    // Ejecutar la consulta de inserción
    if (!mysqli_query($vConexion, $SQL_Insert)) {
        return false;
    } else {
        // Verificar si el tipo de sanción es 3, 4 o 5 (suspensiones)
        $tipos_que_inactivan = [3, 4, 5]; // IDs que inhabilitan al empleado

        if (in_array((int)$_POST['tipo'], $tipos_que_inactivan)) {
            // Modificar el estado del empleado a 0 (inactivo)
            Modificar_Estado_Empleado($_POST['empleado'], 0, $vConexion);
        }
    }

    return true;
}

function Modificar_Estado_Empleado($Id, $Estado, $conexion)
{
    // Actualizar el estado del empleado
    $SQL = "UPDATE empleado AS E SET E.estado = $Estado WHERE E.idempleado = $Id";

    // Ejecutar la consulta de actualización
    if (!mysqli_query($conexion, $SQL)) {
        return false;
    }
    return true;
}
