<?php
function Validar_Datos()
{
    $vMensaje = '';



    if (empty($_POST['empleado'])) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje = 'Debes seleccionar un empleado. <br />';
    }
    if (empty($_POST['fecha'])) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje = 'Debes ingresar una fecha. <br />';
    }
    if (empty($_POST['horas'])) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje = 'Debes ingresar las horas extras que realizó. <br />';
    }
    if (empty($_POST['hora_inicio'])) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje = 'Debes seleccionar la hora en la que inició. <br />';
    }
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $_POST['hora_inicio'])) {
        $vMensaje .= 'La hora de inicio no tiene un formato válido. <br />';
    }




    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]);
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;
}
