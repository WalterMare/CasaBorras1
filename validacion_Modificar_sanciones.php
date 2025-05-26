<?php
require_once 'conexiondb.php';
$conexion = ConexionBD();

function Validar_Datos() {
    $vMensaje = '';

    if (empty($_POST['IdTipoSancion'])) {
        $vMensaje .= 'Debes seleccionar el tipo de sanción. <br />';
    }
    if (empty($_POST['idEmpleado'])) {
        $vMensaje .= 'Debes seleccionar un empleado. <br />';
    }
    if (empty($_POST['fecha_inicio'])) {
        $vMensaje .= 'Debes ingresar una fecha. <br />';
    }

    // Solo pedimos cantidad de días si no es apercibimiento (1 o 2)
    $idTipo = isset($_POST['IdTipoSancion']) ? (int)$_POST['IdTipoSancion'] : 0;
    if ($idTipo != 1 && $idTipo != 2 && empty($_POST['cantidadDias'])) {
        $vMensaje .= 'Debes ingresar la cantidad de días. <br />';
    }

    if (empty($_POST['descripcion'])) {
        $vMensaje .= 'Debes ingresar una breve descripción de la sanción. <br />';
    }
    if (empty($_POST['idEstadoSancion'])) {
        $vMensaje .= 'Debes seleccionar un estado. <br />';
    }

    // Limpieza
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($Valor);
        $_POST[$Id] = strip_tags($Valor);
    }

    return $vMensaje;
}

function ModificarSancion($conexion, $idSancion) {
    $idTipo = (int)$_POST['IdTipoSancion'];

    // Apercibimientos (verbal o escrito) => 0 días
    if ($idTipo == 1 || $idTipo == 2) {
        $_POST['cantidadDias'] = 0;
    }

    $idEmpleado = (int)$_POST['idEmpleado'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $cantidadDias = (int)$_POST['cantidadDias'];
    $descripcion = $_POST['descripcion'];
    $idEstado = (int)$_POST['idEstadoSancion'];

    // Calculamos fecha fin
    $fecha_fin = date('Y-m-d', strtotime($fecha_inicio . ' + ' . $cantidadDias . ' days'));

    $sql = "UPDATE sancion SET 
                idTipoSancion = ?, 
                idEmpleado = ?, 
                fecha_inicio = ?, 
                cantidadDias = ?, 
                descripcion = ?, 
                idEstadoSancion = ?, 
                fecha_fin = ?
            WHERE idsancion = ?";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("iisisisi", 
        $idTipo, 
        $idEmpleado, 
        $fecha_inicio, 
        $cantidadDias, 
        $descripcion, 
        $idEstado, 
        $fecha_fin, 
        $idSancion
    );

    return $stmt->execute();
}
?>

