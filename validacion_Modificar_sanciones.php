<?php
function Validar_Datos() {
    $vMensaje='';
    

    if (empty($_POST['IdTipoSancion'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar el tipo de sanción. <br />';
    }
    if (empty($_POST['idEmpleado'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar un empleado. <br />';
    }
    if (empty($_POST['fecha_inicio'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar una fecha. <br />';
    }
    if (empty($_POST['cantidadDias'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar la cantidad de días. <br />';
    }
    if (empty($_POST['descripcion'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar una breve descripcion de la sanción. <br />';
    }
    if (empty($_POST['idEstadoSancion'])) {//strlen cuenta la cantidad de caracteres de la cadena
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