<?php
function Validar_Datos() {
    $vMensaje='';
    

    if (empty($_POST['nombre'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar el nombre. <br />';
    }
    if (empty($_POST['apellido'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar el apellido. <br />';
    }
    if (empty($_POST['documento'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar el documento de identidad. <br />';
    }
    if (empty($_POST['direccion'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar la direccion. <br />';
    }
    if (empty($_POST['ciudad'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar la ciudad. <br />';
    }
    if (empty($_POST['provincia'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar una provincia. <br />';

    }if (empty($_POST['email'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar el email. <br />';
    }
    if (empty($_POST['tel'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar el numero de telefono. <br />';
    }
    if (empty($_POST['fechanacimiento'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar la fecha de nacimiento. <br />';
    }
    if (empty($_POST['estadocivil'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar el estado civil. <br />';
    }
    if (empty($_POST['sexo'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar el sexo. <br />';
    }
    if (empty($_POST['fechainicio'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes ingresar la fecha de ingreso. <br />';
    }
    if (empty($_POST['cargo'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje='Debes seleccionar el cargo que va a desempeñar. <br />';
    }
   

    

    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach($_POST as $Id=>$Valor){
        $_POST[$Id] = trim($_POST[$Id]);
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;

}

?>