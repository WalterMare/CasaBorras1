<?php
function Validar_Datos() {
    $vMensaje='';
    

  
    if (empty($_POST['empleado'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar un empleado. <br />';
    }
    if (empty($_POST['fecha'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar la fecha de registro. <br />';
    }
    if (empty($_POST['monto'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar el monto del decuento. <br />';
    }
    if (empty($_POST['descripcion'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar una breve descripción del embargo. <br />';
    }
    

    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach($_POST as $Id=>$Valor){
        $_POST[$Id] = trim($_POST[$Id]);
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;

}

?>