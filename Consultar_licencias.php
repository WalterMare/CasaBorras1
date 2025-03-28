<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si el elemento de sesión 'Usuario_Nombre' está vacío, redirigir al login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();  // Conexion a la base de datos

require_once 'select_empleado.php';
$listadoEmpleado = Listar_empleado($conexion);
$CantidadEmpleado = count($listadoEmpleado);

require_once 'select_reporte.php';
$listadoReporte = Listar_TipoReporte($conexion);
$Cantidadreporte = count($listadoReporte);


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Casa Borras</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">


    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
  
    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

</head>

<body>
    <h5 class="card-title">Buscar Licencia </h5>
    <!-- Mensajes de éxito o advertencia -->
    <?php if (!empty($Mensaje)): ?>
        <div id='cartel' class="alert alert-<?= $Estilo ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $Estilo == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-1"></i>
            <?= $Mensaje ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de Registro -->
    <form class="row g-6" method="GET" action="Listado_licencia_empleado.php">

        <div class="col-6">
            <label for="empleado" class="form-label">Seleccionar Empleado:</label>
            <select class="form-select" id="empleado" name="empleado" required>
                <option value="">Selecciona una opción</option>
                <?php foreach ($listadoEmpleado as $empleado): ?>
                    <option value="<?= $empleado['ID']; ?>" <?= (isset($_POST['empleado']) && $_POST['empleado'] == $empleado['ID']) ? 'selected' : ''; ?>>
                        <?= $empleado['NOMBRE'] . " " . $empleado['APELLIDO']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-2 row g-3 text-center">
            <button class="btn btn-primary" type="submit" value="Generar Reporte">Buscar</button>
        </div>
    </form>
</body>
</html>