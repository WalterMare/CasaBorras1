<?php
require_once 'conexiondb.php';
$MiConexion = ConexionBD();

$Mensaje = '';
$TokenValido = false;
$email = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $SQL = "SELECT email FROM recuperacion_contrasena WHERE token = ? AND usado = 0 AND expiracion > NOW()";
    $stmt = mysqli_prepare($MiConexion, $SQL);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($datosToken = mysqli_fetch_assoc($resultado)) {
        $TokenValido = true;
        $email = $datosToken['email'];
    } else {
        $Mensaje = "El enlace de recuperación no es válido o ha expirado.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_contrasena'])) {
    $token = $_POST['token'];
    $nuevaContrasena = $_POST['nueva_contrasena'];
    $confirmarContrasena = $_POST['confirmar_contrasena'];

    if ($nuevaContrasena !== $confirmarContrasena) {
        $Mensaje = "Las contraseñas no coinciden.";
    } elseif (strlen($nuevaContrasena) < 8) {
        $Mensaje = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        $hashContrasena = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

        // Obtener el usuario asociado al email
        $SQL = "SELECT U.user FROM usuario U JOIN empleado E ON U.IdEmpleado = E.idempleado WHERE E.email = ?";
        $stmt = mysqli_prepare($MiConexion, $SQL);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario = mysqli_fetch_assoc($resultado)) {
            // Actualizar la contraseña en la tabla usuario
            $SQL = "UPDATE usuario SET clave = ? WHERE user = ?";
            $stmt = mysqli_prepare($MiConexion, $SQL);
            mysqli_stmt_bind_param($stmt, "ss", $hashContrasena, $usuario['user']);
            mysqli_stmt_execute($stmt);

            // Marcar el token como usado
            $SQL = "UPDATE recuperacion_contrasena SET usado = 1 WHERE token = ?";
            $stmt = mysqli_prepare($MiConexion, $SQL);
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);

            $Mensaje = "Tu contraseña ha sido cambiada con éxito.";
            $TokenValido = false;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Casa Borras</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.php" class="logo d-flex align-items-center w-auto">
                                    <img src="assets/img/LOGO.webp" alt="">
                                    <span class="d-none d-lg-block">Nueva Contraseña</span>
                                </a>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <?php if (!empty($Mensaje)): ?>
                                        <div class="alert alert-info">
                                            <?php echo $Mensaje; ?>
                                        </div>
                                        <?php if (!$TokenValido): ?>
                                            <div class="text-center mt-3">
                                                <a href="login.php" class="btn btn-primary">Volver al login</a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($TokenValido): ?>
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Establecer nueva contraseña</h5>
                                        <p class="text-center small">Ingresa y confirma tu nueva contraseña</p>
                                    </div>

                                    <form class="row g-3 needs-validation" method="post" novalidate>
                                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                                        
                                        <div class="col-12">
                                            <label for="nueva_contrasena" class="form-label">Nueva contraseña</label>
                                            <input type="password" name="nueva_contrasena" class="form-control" id="nueva_contrasena" required minlength="8">
                                            <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="confirmar_contrasena" class="form-label">Confirmar contraseña</label>
                                            <input type="password" name="confirmar_contrasena" class="form-control" id="confirmar_contrasena" required minlength="8">
                                            <div class="invalid-feedback">Por favor confirma tu contraseña.</div>
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn-primary w-100" type="submit" name="cambiar_contrasena">Cambiar contraseña</button>
                                        </div>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>