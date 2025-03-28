<?php
function Validar_Datos() {
    $vMensaje='';
    

    if (empty($_POST['tipo'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar el tipo de sanción. <br />';
    }
    if (empty($_POST['empleado'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar un empleado. <br />';
    }
    if (empty($_POST['fecha'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar una fecha. <br />';
    }
    if ($_POST['tipo'] != '1' && $_POST['tipo'] != '2') {
        if (empty($_POST['dias']) || $_POST['dias'] <= 0) {
            $vMensaje .= 'Este tipo de sanción requiere una cantidad de días mayor que 0.<br>';
        }
    }
    if (empty($_POST['descripcion'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar una breve descripcion de la sanción. <br />';
    }
    if (empty($_POST['estado'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar un estado. <br />';
    }
  

    

    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach($_POST as $Id=>$Valor){
        $_POST[$Id] = trim($_POST[$Id]);
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;

}

?>