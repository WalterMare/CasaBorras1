<?php
function Modificar_usuario($vConexion, $idusuario)
{
    if (!isset($_POST['user'], $_POST['Idtipo'])) {
        die('<h4>Error: Faltan datos en el formulario.</h4>');
    }

    $usuario_modificado = $_POST['user'];
    $tipo_modificado = $_POST['Idtipo'];

    // Actualizar usuario
    $SQL_Update = "UPDATE usuario SET user = ?, Idtipo = ? WHERE idusuario = ?";
    $stmt = mysqli_prepare($vConexion, $SQL_Update);

    if (!$stmt) {
        die('<h4>Error al preparar la consulta.</h4>');
    }

    mysqli_stmt_bind_param($stmt, "sii", $usuario_modificado, $tipo_modificado, $idusuario);

    if (!mysqli_stmt_execute($stmt)) {
        die('<h4>Error al intentar actualizar el usuario.</h4>');
    }

    mysqli_stmt_close($stmt);

    // ---------------------------------------------
    // Actualizar roles funcionales del usuario
    // ---------------------------------------------

    // Eliminar todos los roles anteriores
    $SQL_Delete = "DELETE FROM usuario_rol_funcional WHERE idusuario = ?";
    $stmt_del = mysqli_prepare($vConexion, $SQL_Delete);
    mysqli_stmt_bind_param($stmt_del, "i", $idusuario);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    // Insertar los nuevos roles seleccionados
    if (!empty($_POST['roles']) && is_array($_POST['roles'])) {
        foreach ($_POST['roles'] as $idrol) {
            $SQL_Insert = "INSERT INTO usuario_rol_funcional (idusuario, idrol_funcional) VALUES (?, ?)";
            $stmt_ins = mysqli_prepare($vConexion, $SQL_Insert);
            mysqli_stmt_bind_param($stmt_ins, "ii", $idusuario, $idrol);
            if (!mysqli_stmt_execute($stmt_ins)) {
                die('<h4>Error al insertar nuevo rol funcional.</h4>');
            }
            mysqli_stmt_close($stmt_ins);
        }
    }

    return true;
}
?>


