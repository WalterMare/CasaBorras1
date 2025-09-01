<?php
require_once 'conexiondb.php';
require 'vendor/autoload.php'; // Cargar PHPMailer correctamente

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$MiConexion = ConexionBD();
$Mensaje = '';
$MostrarFormulario = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_recuperacion'])) {
    $email = trim($_POST['email']);

    // Verificar si el email existe en la tabla empleado y tiene un usuario activo
    $SQL = "SELECT U.idusuario, U.user, E.nombre, E.apellido, E.email, E.estado
            FROM usuario U
            JOIN empleado E ON U.IdEmpleado = E.idempleado
            WHERE E.email = ?";

    $stmt = mysqli_prepare($MiConexion, $SQL);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        if ($usuario['estado'] == 0) {
            $Mensaje = "Tu cuenta está inactiva. Contacta al administrador.";
        } else {
            // Verificar si ya existe un token activo
            $SQL = "SELECT id FROM recuperacion_contrasena WHERE email = ? AND expiracion > NOW() AND usado = 0";
            $stmt = mysqli_prepare($MiConexion, $SQL);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($resultado) == 0) {
                // Generar nuevo token
                $token = bin2hex(random_bytes(32));
                $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));

                // Guardar en la base de datos
                $SQL = "INSERT INTO recuperacion_contrasena (email, token, expiracion, usado) VALUES (?, ?, ?, 0)";
                $stmt = mysqli_prepare($MiConexion, $SQL);
                mysqli_stmt_bind_param($stmt, "sss", $email, $token, $expiracion);
                mysqli_stmt_execute($stmt);

                // **Crear enlace de recuperación**
                $enlaceRecuperacion = "https://tudominio.com/recuperar.php?token=" . $token;

                // **Configurar y enviar el correo**
                $mail = new PHPMailer(true);

                try {
                    // Configuración SMTP con Mailtrap
                    $mail->isSMTP();
                    $mail->Host = 'sandbox.smtp.mailtrap.io';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'c8d64ce5d0ac4a'; // tu usuario de Mailtrap
                    $mail->Password = '2fa2556a442707'; // tu password de Mailtrap
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 465; // uno de los puertos válidos (2525 es el más usado)

                    // Configurar remitente y destinatario
                    $mail->setFrom('no-reply@casaborras.com', 'Casa Borras');
                    $mail->addAddress($email, "{$usuario['nombre']} {$usuario['apellido']}");

                    $mail->Subject = 'Recuperación de contraseña - Casa Borras';

                    // Cuerpo del correo
                    $mensajeEmail = "Hola {$usuario['nombre']} {$usuario['apellido']},<br><br>";
                    $mensajeEmail .= "Hemos recibido una solicitud para restablecer tu contraseña.<br>";
                    $mensajeEmail .= "Haz clic en el siguiente enlace para continuar:<br>";
                    $mensajeEmail .= "<a href='$enlaceRecuperacion'>$enlaceRecuperacion</a><br><br>";
                    $mensajeEmail .= "Si no solicitaste este cambio, puedes ignorar este mensaje.<br>";
                    $mensajeEmail .= "Este enlace expirará en 1 hora.<br><br>";
                    $mensajeEmail .= "Atentamente,<br>El equipo de Casa Borras";

                    $mail->isHTML(true);
                    $mail->Body = $mensajeEmail;

                    // Enviar correo
                    $mail->send();
                    $Mensaje = "Se ha enviado un enlace de recuperación a tu correo (revisa tu inbox de Mailtrap).";
                } catch (Exception $e) {
                    $Mensaje = "Error al enviar el correo: " . $mail->ErrorInfo;
                }
            } else {
                $Mensaje = "Ya tienes una solicitud activa de recuperación.";
            }
        }
    } else {
        $Mensaje = "Si el correo está registrado, recibirás un enlace de recuperación.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Casa Borras</title>
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
                                    <span class="d-none d-lg-block">Recuperar Contraseña</span>
                                </a>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <?php if (!empty($Mensaje)): ?>
                                        <div class="alert alert-info">
                                            <?php echo $Mensaje; ?>
                                        </div>
                                        <?php if (!$MostrarFormulario): ?>
                                            <div class="text-center mt-3">
                                                <a href="login.php" class="btn btn-primary">Volver al login</a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($MostrarFormulario): ?>
                                        <div class="pt-4 pb-2">
                                            <h5 class="card-title text-center pb-0 fs-4">Recuperar contraseña</h5>
                                            <p class="text-center small">Ingresa tu correo electrónico registrado</p>
                                        </div>

                                        <form class="row g-3 needs-validation" method="post" novalidate>
                                            <div class="col-12">
                                                <label for="email" class="form-label">Correo electrónico</label>
                                                <div class="input-group has-validation">
                                                    <span class="input-group-text" id="inputGroupPrepend">@</span>
                                                    <input type="email" name="email" class="form-control" id="email" required>
                                                    <div class="invalid-feedback">Ingresa tu correo electrónico.</div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button class="btn btn-primary w-100" type="submit" name="solicitar_recuperacion">Enviar enlace</button>
                                            </div>
                                            <div class="col-12 text-center">
                                                <a href="login.php" class="small">Volver al login</a>
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