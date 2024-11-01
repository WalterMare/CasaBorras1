<?php
function Validar_Datos_Familiar()
{
    $vMensaje = '';

    
    if (empty($_POST['nombre'] )) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje .= 'Debe ingresar el nombre. <br />';
    }
    if (empty($_POST['apellido']) ) {
        $vMensaje .= 'Debe ingresar el apellido. <br />';
    }
    if (strlen($_POST['dni']) < 7 || strlen($_POST['dni']) > 10) {
        $vMensaje .= 'Debe Ingresar el dni correctamente';
    }
   
    if (empty($_POST['relacion']) ) {
        $vMensaje .= 'Para continuar debes seleccionar el tipo de relacion';
    }
    if (empty($_POST['fechanacimiento'])) {
        $vMensaje .= 'Para continuar debes Ingresar la Fecha de Nacimiento';
    }
    if (empty($_POST['tel'])) {
        $vMensaje .= 'Para continuar debes Ingresar un telefono';
    }
    if (empty($_POST['empleado'])) {
        $vMensaje .= 'Para continuar debes seleccionar un empleado al cual se asociara el familiar';
    }





    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]); //limpia los espacios
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;
}
?>
<?php
function Validar_Datos_Familiar_bis()
{
    $vMensaje = '';

    
    if (empty($_POST['nombre'] )) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje .= 'Debe ingresar el nombre. <br />';
    }
    if (empty($_POST['apellido']) ) {
        $vMensaje .= 'Debe ingresar el apellido. <br />';
    }
    if (strlen($_POST['dni']) < 7 || strlen($_POST['dni']) > 10) {
        $vMensaje .= 'Debe Ingresar el dni correctamente';
    }
   
    if (empty($_POST['relacion']) ) {
        $vMensaje .= 'Para continuar debes seleccionar el tipo de relacion';
    }
    if (empty($_POST['fechanacimiento'])) {
        $vMensaje .= 'Para continuar debes Ingresar la Fecha de Nacimiento';
    }
    if (empty($_POST['tel'])) {
        $vMensaje .= 'Para continuar debes Ingresar un telefono';
    }
   

    //con esto aseguramos que limpiamos espacios y limpiamos de caracteres de codigo ingresados
    foreach ($_POST as $Id => $Valor) {
        $_POST[$Id] = trim($_POST[$Id]); //limpia los espacios
        $_POST[$Id] = strip_tags($_POST[$Id]); //limpia los caracteres
    }


    return $vMensaje;
}
