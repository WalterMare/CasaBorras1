<?php
function Modificar_usuario($vConexion, $idusuario)
{
    // Verificar que se recibieron los datos correctamente
    if (!isset($_POST['user'], $_POST['Idtipo'])) {
        die('<h4>Error: Faltan datos en el formulario.</h4>');
    }

    // Obtener datos del formulario
    $usuario_modificado = $_POST['user'];
    $tipo_modificado = $_POST['Idtipo'];

    // Consulta preparada para actualizar el usuario
    $SQL_Update = "UPDATE usuario SET user = ?, Idtipo = ? WHERE idusuario = ?";
    $stmt = mysqli_prepare($vConexion, $SQL_Update);
    
    if (!$stmt) {
        die('<h4>Error al preparar la consulta.</h4>');
    }

    // Enlazar parámetros a la consulta (s -> string, i -> integer)
    mysqli_stmt_bind_param($stmt, "sii", $usuario_modificado, $tipo_modificado, $idusuario);

    // Ejecutar la consulta
    if (!mysqli_stmt_execute($stmt)) {
        die('<h4>Error al intentar actualizar el usuario.</h4>');
    }

    // Cerrar la consulta preparada
    mysqli_stmt_close($stmt);

    return true;
}

