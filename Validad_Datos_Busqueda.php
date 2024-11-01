<?php
function Validar_Datos_busqueda()
{
    $vMensaje = '';

    
    if (empty($_POST['empleado'] )) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje .= 'Datos no encontrados debe seleccionar una opción. <br />';
    }
    
   
   

    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]); //limpia los espacios
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;
}