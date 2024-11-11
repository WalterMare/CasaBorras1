<?php
function DatosLogin($vUsuario, $vClave, $vConexion){
    $Usuario = array();

    // Consulta de usuario
    $SQL = "SELECT U.idusuario, U.user, U.clave, U.IdEmpleado, U.Idtipo, E.idempleado, E.nombre, E.apellido, E.estado, E.imagen, T.idtipo, T.descripcion
            FROM usuario U
            JOIN empleado E ON U.IdEmpleado = E.idempleado
            JOIN tipo T ON U.Idtipo = T.idtipo
            WHERE U.user = ?";

    $stmt = mysqli_prepare($vConexion, $SQL);
    mysqli_stmt_bind_param($stmt, "s", $vUsuario);  // 's' indica que es una cadena
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_array($rs);

    if (!empty($data)) {
        // Si la contraseña está en texto plano, la comparamos directamente
        if ($vClave === $data['clave']) {
            // Si la contraseña es correcta, migramos a un hash seguro
            $hashedPassword = password_hash($vClave, PASSWORD_DEFAULT);

            // Actualizamos la contraseña en la base de datos con el nuevo hash
            $updateSQL = "UPDATE usuario SET clave = ? WHERE idusuario = ?";
            $updateStmt = mysqli_prepare($vConexion, $updateSQL);
            mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $data['idusuario']);
            mysqli_stmt_execute($updateStmt);

            // Procedemos con la autenticación normal
            $Usuario['NOMBRE'] = $data['nombre'];
            $Usuario['APELLIDO'] = $data['apellido'];
            $Usuario['TIPO'] = $data['Idtipo'];
            $Usuario['ESTADO'] = $data['estado'];

            // Si no tiene imagen, asignamos una predeterminada
            if (empty($data['imagen'])) {
                $data['imagen'] = "profile.jpg"; 
            }
            $Usuario['IMG'] = $data['imagen'];

            // Otros datos del usuario
            $Usuario['ID'] = $data['idusuario'];
            $Usuario['NOMBRE_TIPO'] = $data['descripcion'];
        } else {
            // Si la contraseña es incorrecta
            return false;
        }
    } else {
        // Si no se encuentra el usuario
        return false;
    }

    return $Usuario;
}
?>

