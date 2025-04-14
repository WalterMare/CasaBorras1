<?php
function DatosLogin($vUsuario, $vClave, $vConexion) {
    $Usuario = array();

    // Consulta de usuario
    $SQL = "SELECT U.idusuario, U.user, U.clave, U.IdEmpleado, U.Idtipo, 
                   E.idempleado, E.nombre, E.apellido, E.estado, E.imagen, 
                   T.idtipo, T.descripcion
            FROM usuario U
            JOIN empleado E ON U.IdEmpleado = E.idempleado
            JOIN tipo T ON U.Idtipo = T.idtipo
            WHERE U.user = ?";

    $stmt = mysqli_prepare($vConexion, $SQL);
    mysqli_stmt_bind_param($stmt, "s", $vUsuario);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_array($rs);

    if (!empty($data)) {
        // Verificación de contraseña con soporte para MD5 (legacy) y Argon2
        $claveValida = false;
        
        // 1. Primero intentamos con Argon2 (password_verify)
        if (password_verify($vClave, $data['clave'])) {
            $claveValida = true;
        } 
        // 2. Si falla, verificamos si es MD5 (solo para migración)
        elseif (strlen($data['clave']) === 32 && ctype_xdigit($data['clave']) && 
                md5($vClave) === $data['clave']) {
            $claveValida = true;
            
            // Migramos a Argon2 automáticamente
            $nuevoHash = password_hash($vClave, PASSWORD_ARGON2I, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 2
            ]);
            
            $SQL_Update = "UPDATE usuario SET clave = ? WHERE idusuario = ?";
            $stmt_update = mysqli_prepare($vConexion, $SQL_Update);
            mysqli_stmt_bind_param($stmt_update, "si", $nuevoHash, $data['idusuario']);
            mysqli_stmt_execute($stmt_update);
        }

        if ($claveValida) {
            $Usuario['NOMBRE'] = $data['nombre'];
            $Usuario['APELLIDO'] = $data['apellido'];
            $Usuario['TIPO'] = $data['Idtipo'];
            $Usuario['ESTADO'] = $data['estado'];

            // Manejo de imagen (mejorado)
            if (empty($data['imagen']) || $data['imagen'] === '') {
                $Usuario['IMG'] = base64_encode(file_get_contents('assets/img/profile.jpg'));
            } else {
                $Usuario['IMG'] = base64_encode($data['imagen']);
            }

            $Usuario['ID'] = $data['idusuario'];
            $Usuario['IDEMPLEADO'] = $data['idempleado'];
            $Usuario['NOMBRE_TIPO'] = $data['descripcion'];
            
            return $Usuario;
        }
    }
    
    return false;
}
?>


