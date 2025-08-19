<?php
function Validar_Datos()
{
    $vMensaje = '';



    if (empty($_POST['empleado'])) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje .= 'Datos no encontrados debe seleccionar una opción. <br />';
    }
    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]); //limpia los espacios
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;
}
function Validar_DatosRegistro()
{


    $vMensaje = '';

    // Limpiamos espacios y etiquetas de todo el POST
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($Valor); // limpia espacios
        $_POST[$Id] = strip_tags($_POST[$Id]); // limpia etiquetas HTML
    }

    // Validar que haya empleado
    if (empty($_POST['empleadoid'])) {
        $vMensaje .= 'Debe seleccionar un empleado.<br />';
    }

    // Validar antigüedad (aunque viene readonly, igual chequeamos)
    if (!isset($_POST['antiguedad']) || !is_numeric($_POST['antiguedad'])) {
        $vMensaje .= 'Antigüedad no válida.<br />';
    }

    // Validar fecha de inicio
    if (empty($_POST['fecha_inicio'])) {
        $vMensaje .= 'Debe ingresar la fecha de inicio.<br />';
    }

    // Validar fecha de fin
    if (empty($_POST['fecha_fin'])) {
        $vMensaje .= 'Debe ingresar la fecha de fin.<br />';
    }

    // Si ambas fechas existen, validar que inicio <= fin
    if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
        $inicio = strtotime($_POST['fecha_inicio']);
        $fin = strtotime($_POST['fecha_fin']);
        if ($inicio > $fin) {
            $vMensaje .= 'La fecha de inicio no puede ser posterior a la fecha de fin.<br />';
        }
    }

    // Validar cantidad de días seleccionados
    if (empty($_POST['cantidad_dias']) || $_POST['cantidad_dias'] <= 0) {
        $vMensaje .= 'Cantidad de días seleccionados inválida.<br />';
    }

    // Validar que no se superen los días por antigüedad
    if (!empty($_POST['dias_antiguedad']) && !empty($_POST['cantidad_dias'])) {
        if ($_POST['cantidad_dias'] > $_POST['dias_antiguedad']) {
            $vMensaje .= 'La cantidad de días seleccionados excede los días disponibles según antigüedad.<br />';
        }
    }

    // Observaciones (opcional, pero limpiamos igual)
    if (isset($_POST['observaciones']) && strlen($_POST['observaciones']) > 255) {
        $vMensaje .= 'Las observaciones no pueden superar los 255 caracteres.<br />';
    }

    return $vMensaje;
}
