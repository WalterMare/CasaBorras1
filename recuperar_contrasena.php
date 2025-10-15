<?php
require_once 'conexiondb.php';
$MiConexion = ConexionBD();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $nueva = trim($_POST['nueva_contrasena']);
    $confirmar = trim($_POST['confirmar_contrasena']);

    if ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (strlen($nueva) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        // Buscar el usuario según el email
        $SQL = "SELECT U.idusuario 
                FROM usuario U 
                JOIN empleado E ON U.IdEmpleado = E.idempleado 
                WHERE E.email = ?";
        $stmt = mysqli_prepare($MiConexion, $SQL);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario = mysqli_fetch_assoc($resultado)) {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $SQL = "UPDATE usuario SET clave = ? WHERE idusuario = ?";
            $stmt = mysqli_prepare($MiConexion, $SQL);
            mysqli_stmt_bind_param($stmt, "si", $hash, $usuario['idusuario']);
            mysqli_stmt_execute($stmt);

            $mensaje = "✅ Contraseña actualizada correctamente.";
        } else {
            $mensaje = "❌ No se encontró ningún usuario con ese correo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="text-center mb-4">Recuperar Contraseña</h4>
                        <?php if ($mensaje): ?>
                            <div class="alert alert-info"><?php echo $mensaje; ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="nueva_contrasena" class="form-label">Nueva contraseña</label>
                                <input type="password" name="nueva_contrasena" class="form-control" required minlength="8">
                            </div>

                            <div class="mb-3">
                                <label for="confirmar_contrasena" class="form-label">Confirmar contraseña</label>
                                <input type="password" name="confirmar_contrasena" class="form-control" required minlength="8">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Cambiar contraseña</button>
                            <div class="text-center mt-3">
                                <a href="login.php">Volver al login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>