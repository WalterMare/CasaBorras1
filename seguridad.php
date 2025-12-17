<?php

// Función de control de acceso
function TieneAccesos($nivelUsuario, $rolesFuncionales, $rolesNecesarios = [])
{
    // Administrador → acceso total
    if ($nivelUsuario == 1) {
        return true;
    }

    // Si no hay roles requeridos → no accede
    if (empty($rolesNecesarios)) {
        return false;
    }

    // Verificar si tiene al menos un rol requerido
    return count(array_intersect($rolesFuncionales, $rolesNecesarios)) > 0;
}
