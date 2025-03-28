<?php
function Validar_Datos() {
    $vMensaje = '';

    if (empty($_POST['estado'])) { // Verificar si se seleccionó el estado
        $vMensaje .= 'Debes seleccionar el estado. <br />';
    }

    if (empty($_POST['empleado'])) { // Verificar si se seleccionó un empleado
        $vMensaje .= 'Debes seleccionar un empleado. <br />';
    }

    if (empty($_POST['fecha_inicio'])) { // Verificar si se ingresó la fecha de inicio
        $vMensaje .= 'Debes ingresar una fecha de inicio. <br />';
    } else {
        // Convertir la fecha de inicio a formato DateTime
        $fechaInicio = new DateTime($_POST['fecha_inicio']);
        $mesInicio = $fechaInicio->format('m');
        $anioInicio = $fechaInicio->format('Y');

        // Validar que la fecha de inicio esté entre octubre (10) y abril (4) del siguiente año
        if ($mesInicio < 10 && $mesInicio > 4) {
            $vMensaje .= 'La fecha de inicio debe estar entre octubre y abril del siguiente año. <br />';
        }
    }

    if (empty($_POST['fecha_fin'])) { // Verificar si se ingresó la fecha de fin
        $vMensaje .= 'Debes ingresar la fecha final. <br />';
    } else {
        // Convertir la fecha de fin a formato DateTime
        $fechaFin = new DateTime($_POST['fecha_fin']);
        $mesFin = $fechaFin->format('m');
        $anioFin = $fechaFin->format('Y');

        // Validar que la fecha de fin esté dentro del rango permitido (hasta el 30 de abril)
        if ($mesFin > 4) {
            $vMensaje .= 'La fecha de fin debe estar antes del 1 de mayo. <br />';
        }

        // Validar que la fecha de fin no sea anterior a la fecha de inicio
        if ($fechaFin < $fechaInicio) {
            $vMensaje .= 'La fecha de fin no puede ser anterior a la fecha de inicio. <br />';
        }
    }

    if (empty($_POST['año'])) { // Verificar si se ingresó el año de la licencia
        $vMensaje .= 'Debes ingresar el año de la licencia. <br />';
    }

    // Limpiar los valores de $_POST de caracteres no deseados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]);
        $_POST[$Id] = strip_tags($_POST[$Id]); // Limpiar caracteres especiales
    }

    return $vMensaje;
}
?>


