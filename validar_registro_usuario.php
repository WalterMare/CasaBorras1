<?php
function Validar_Datos_Usuario()
{
    $vMensaje = '';

    
    if (empty($_POST['usuario'] )) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje .= 'Debe ingresar un usuario. <br />';
    }
    if (empty($_POST['clave'])  ) {
        $vMensaje .= 'Debe ingresar la clave. <br />';
    }
    if (empty($_POST['clave1'])  ) {
        $vMensaje .= 'Debe reingresar la clave. <br />';
    }
    if (strlen($_POST['clave1']) < 6 || strlen($_POST['clave'] <6) ) {
        $vMensaje .= 'Debe Ingresar una clave correcta';
    }

    if ($_POST['clave'] != $_POST['clave1']   ) {
        $vMensaje .= 'Las claves no coinciden. <br />';
    }
   
    if (empty($_POST['empleado']) ) {
        $vMensaje .= 'Para continuar debes seleccionar el tipo de relacion';
    }
    if (empty($_POST['tipo'])) {
        $vMensaje .= 'Para continuar debes Ingresar la Fecha de Nacimiento';
    }
    



    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]); //limpia los espacios
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;
}
