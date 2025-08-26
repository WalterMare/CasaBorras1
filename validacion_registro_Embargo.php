<?php
function Validar_Datos()
{
    $vMensaje = '';

    // Empleado obligatorio
    if (empty($_POST['empleado'])) {
        $vMensaje .= 'Debes seleccionar un empleado. <br />';
    }

    // Fecha de registro obligatoria
    if (empty($_POST['fecha'])) {
        $vMensaje .= 'Debes ingresar la fecha de registro. <br />';
    }

    // Expediente obligatorio
    if (empty($_POST['expediente'])) {
        $vMensaje .= 'Debes ingresar el número de expediente. <br />';
    }

    // Tipo obligatorio
    if (empty($_POST['tipo'])) {
        $vMensaje .= 'Debes seleccionar el tipo de embargo. <br />';
    }

    // Fecha de inicio obligatoria
    if (empty($_POST['fecha_inicio'])) {
        $vMensaje .= 'Debes ingresar la fecha de inicio del embargo. <br />';
    }

    // Validación lógica de fechas
    if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
        if ($_POST['fecha_fin'] < $_POST['fecha_inicio']) {
            $vMensaje .= 'La fecha de fin no puede ser anterior a la fecha de inicio. <br />';
        }
    }

    // Monto o porcentaje → al menos uno debe estar cargado
    if (empty($_POST['monto']) && empty($_POST['porcentaje'])) {
        $vMensaje .= 'Debes ingresar un monto fijo o un porcentaje. <br />';
    }

    // No se puede ingresar ambos simultáneamente
    if (!empty($_POST['monto']) && !empty($_POST['porcentaje'])) {
        $vMensaje .= 'Solo puedes ingresar monto o porcentaje, no ambos. <br />';
    }

    // Si monto está cargado, que sea > 0
    if (!empty($_POST['monto']) && $_POST['monto'] <= 0) {
        $vMensaje .= 'El monto debe ser mayor que 0. <br />';
    }

    // Si porcentaje está cargado, que esté entre 1 y 100
    if (
        !empty($_POST['porcentaje']) &&
        ($_POST['porcentaje'] <= 0 || $_POST['porcentaje'] > 100)
    ) {
        $vMensaje .= 'El porcentaje debe estar entre 1 y 100. <br />';
    }

    // Descripción obligatoria
    if (empty($_POST['descripcion'])) {
        $vMensaje .= 'Debes ingresar una breve descripción del embargo. <br />';
    }

    // Limpieza de entradas
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($Valor);
        $_POST[$Id] = strip_tags($Valor);
    }

    return $vMensaje;
}
