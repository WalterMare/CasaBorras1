<?php
function Validar_Datos_viaje() {
    $vMensaje='';
    
    if (empty($_POST['usuario']) || $_POST['usuario']==0) {
        $vMensaje.='Debe seleccionar un usuario. <br />';
    }
    if (empty($_POST['transporte'])|| $_POST['transporte']==0) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje.='Debes seleccionar el transporte. <br />';
    }
    if (empty($_POST['fecha'])) {//strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje.='Debes ingresar la fecha. <br />';
    }
    if (empty($_POST['destino'])){
        $vMensaje='Debes selecionar un destino';
    }
    if (empty($_POST['costo'])){
        $vMensaje='Debes Ingresar el costo del viaje';
    }
    if (empty($_POST['porcentaje'])){
        $vMensaje='Debes Ingresar porcentaje del pago al chofer';
    }
    

    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach($_POST as $Id=>$Valor){
        $_POST[$Id] = trim($_POST[$Id]);
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;

}

?>