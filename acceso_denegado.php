<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso denegado</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <h1 class="text-danger">Acceso denegado</h1>
        <p>No tiene permisos para acceder a este módulo.</p>
        <a href="index.php" class="btn btn-primary">Volver al panel</a>
    </div>
</body>
</html>