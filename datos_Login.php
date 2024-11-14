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
        // Verificar la contraseña usando password_verify (para contraseñas almacenadas como hash)
        if (password_verify($vClave, $data['clave'])) {
            // Si la contraseña es correcta, procedemos con la autenticación

            // No es necesario actualizar el hash de la contraseña en cada login

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


