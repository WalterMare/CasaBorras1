<?php
function Validar_Datos_Usuario()
{
    $vMensaje = '';


    if (empty($_POST['usuario'])) { //strlen cuenta la cantidad de caracteres de la cadena
        $vMensaje .= 'Debe ingresar un usuario. <br />';
    }
    if (empty($_POST['clave'])) {
        $vMensaje .= 'Debe ingresar la clave. <br />';
    }
    if (empty($_POST['clave1'])) {
        $vMensaje .= 'Debe reingresar la clave. <br />';
    }
    if (strlen($_POST['clave1']) < 6 || strlen($_POST['clave'] < 6)) {
        $vMensaje .= 'Debe Ingresar una clave correcta. <br />';
    }

    if ($_POST['clave'] != $_POST['clave1']) {
        $vMensaje .= 'Las claves no coinciden. <br />';
    }

    if (empty($_POST['empleado'])) {
        $vMensaje .= 'Para continuar debes seleccionar un empleado. <br />';
    }
    if (empty($_POST['tipo'])) {
        $vMensaje .= 'Para continuar debes elejir el tipo de usuario. <br />';
    }
    $roles = $_POST['roles'] ?? [];

    if (intval($_POST['tipo']) === 1 && count($roles) > 0) {
        $vMensaje .= 'Un usuario Administrador no puede tener roles funcionales seleccionados. <br />';
    }

    if (intval($_POST['tipo']) === 2 && count($roles) === 0) {
        $vMensaje .= 'Un usuario Operador debe tener roles funcionales seleccionados. <br />';
    }

    foreach ($_POST as $Id => $Valor) {
        if (is_string($Valor)) {
            $_POST[$Id] = trim($Valor);
            $_POST[$Id] = strip_tags($_POST[$Id]);
        }
    }
    return $vMensaje;
}


function Validar_Datos_Usuario_bis($usuario_modificado, $tipo_modificado, $roles_seleccionados = [])
{
    $vMensaje = '';

    // Validar que el campo de usuario no esté vacío
    if (empty($usuario_modificado)) {
        $vMensaje .= 'Debe ingresar un usuario. <br />';
    }

    // Validar que el tipo no esté vacío
    if (empty($tipo_modificado)) {
        $vMensaje .= 'Debe seleccionar un tipo. <br />';
    }

    // Limpiar los datos ingresados
    $usuario_modificado = trim($usuario_modificado); // Eliminar espacios al inicio y final
    $usuario_modificado = strip_tags($usuario_modificado); // Eliminar etiquetas HTML

    $tipo_modificado = trim($tipo_modificado); // Eliminar espacios al inicio y final
    $tipo_modificado = strip_tags($tipo_modificado); // Eliminar etiquetas HTML

    // Validación adicional para Administrador (tipo 1)
    if (intval($tipo_modificado) == 1 && !empty($roles_seleccionados)) {
        $vMensaje .= 'Un usuario Administrador no puede tener roles funcionales seleccionados. <br />';
    }
    // Validación adicional para Administrador (tipo 1)
    if (intval($tipo_modificado) == 2 && empty($roles_seleccionados)) {
        $vMensaje .= 'Un usuario Operador debe tener roles funcionales seleccionados. <br />';
    }

    return $vMensaje;
}
