
<?php
session_start();

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

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php'; ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>
    <!-- End Sidebar -->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Registrar Reporte</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de reportes</li>
                    <li class="breadcrumb-item active">Registrar Reportes</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ingresa los datos</h5>

                            <!-- Mensajes de éxito o advertencia -->
                            <?php if (!empty($Mensaje)): ?>
                                <div id='cartel' class="alert alert-<?= $Estilo ?> alert-dismissible fade show" role="alert">
                                    <i class="bi bi-<?= $Estilo == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-1"></i>
                                    <?= $Mensaje ?>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario de Registro -->
                            <form class="row g-3" method="GET" action="Insert_reporte.php">
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

                                <div class="col-6">
                                    <label for="tipo_reporte" class="form-label">Seleccionar Tipo de Reporte:</label>
                                    <select class="form-select" id="tipo_reporte" name="tipo_reporte" required>
                                        <option value="">Selecciona una opción</option>
                                        <?php foreach ($listadoReporte as $reporte): ?>
                                            <option value="<?= $reporte['ID']; ?>" <?= (isset($_POST['tipo']) && $_POST['tipo'] == $reporte['ID']) ? 'selected' : ''; ?>>
                                                <?= $reporte['DESCRIPCION']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                
                                <div class="text-center">
                                    <button class="btn btn-primary" type="submit" value="Generar Reporte" >Generar</button>
                                    <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
                                    <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>

</body>

</html>