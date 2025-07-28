<?php
function InsertarUsuario($vConexion) {
    // Datos del formulario
    $usuario = $_POST['usuario'];
    $claveHash = password_hash($_POST['clave'], PASSWORD_ARGON2I, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 2
    ]);
    $idempleado = $_POST['empleado'];
    $idtipo = $_POST['tipo'];

    // Consulta segura
    $SQL_Insert = "INSERT INTO usuario(user, clave, IdEmpleado, Idtipo) VALUES (?, ?, ?, ?)";
    $stmt = $vConexion->prepare($SQL_Insert);
    $stmt->bind_param("ssii", $usuario, $claveHash, $idempleado, $idtipo);

    if ($stmt->execute()) {
        $idusuario = $stmt->insert_id;

        // Insertar roles funcionales si hay
        if (!empty($_POST['roles'])) {
            foreach ($_POST['roles'] as $idrol) {
                $sql_rol = "INSERT INTO usuario_rol_funcional (idusuario, idrol_funcional) VALUES (?, ?)";
                $stmt_rol = $vConexion->prepare($sql_rol);
                $stmt_rol->bind_param("ii", $idusuario, $idrol);
                if (!$stmt_rol->execute()) {
                    // Error al insertar un rol
                    return false;
                }
            }
        }

        return true;
    } else {
        return false;
    }
}
?>
