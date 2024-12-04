<?php
session_start();

// Verificar si el usuario está logueado
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';

// Conexión a la base de datos
$conexion = ConexionBD();

// Inicialización de variables para mensajes
$mensaje = '';
$estilo = 'info';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Casa Borras</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <!--<link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
-->
    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
    <title>Registrar Licencias y Detalles</title>
    <script>
        function agregarDetalle() {
            const container = document.getElementById('detallesContainer');
            const detalleHTML = `
            <br> </br>
                <div class="detalle">
                <div class="col-6">
                    <label class="form-control">Descripción del Detalle:</label>
                    <input class="form-control" type="text" name="detalles_descripcion[]" required>
                </div>
                <div class="col-6">
                    <label class="form-control">Documentación:</label>
                    <input class="form-control" type="file" name="detalles_documentacion[]" accept="application/pdf">
                </div>
                    
                    <button class="btn btn-primary" type="button" onclick="eliminarDetalle(this)">Eliminar</button>
                </div>`;
            container.insertAdjacentHTML('beforeend', detalleHTML);
        }

        function eliminarDetalle(button) {
            button.parentElement.remove();
        }
    </script>
</head>

<body>
    <?php $mensaje = ''; ?>
    <?php include_once 'partes/header.php' ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Registrar Licencias</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de movimientos</li>
                    <li class="breadcrumb-item active">Registrar Licencias</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ingresa los datos</h5>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-1"></i>
                                Los campos indicados con (*) son requeridos
                            </div>
                          

                            <form class="row g-3" action="Registrar_Licencia.php" method="post" enctype="multipart/form-data">
                                <div class="col-6">
                                    <!-- Selección de Empleado -->
                                    <label class="form-label" for="idEmpleado">Seleccione un empleado:</label>
                                    <select class="form-select" id="idEmpleado" name="idEmpleado" required>
                                        <?php
                                        // Conexión a la base de datos
                                        $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');
                                        $stmt = $pdo->query("SELECT idempleado, nombre, apellido FROM empleado WHERE estado = 1");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$row['idempleado']}'>{$row['nombre']} {$row['apellido']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>


                                <div class="col-6">
                                    <!-- Información de la Licencia -->
                                    <label class="form-label" for="fechainicio">Fecha de Inicio:</label>
                                    <input class="form-control" type="date" id="fechainicio" name="fechainicio" required><br>
                                </div>

                                <div class="col-6">
                                    <label class="form-label" for="fechafin">Fecha de Fin:</label>
                                    <input class="form-control" type="date" id='fechafin' name="fechafin" required><br>
                                </div>

                                <div class="col-6">
                                    <label class="form-label" for="IdTipo">Tipo de Licencia:</label>
                                    <select class="form-select" id='IdTipo' name="IdTipo" required>
                                        <?php
                                        $stmt = $pdo->query("SELECT idtipoLicencia, descripcion FROM tipolicencia");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$row['idtipoLicencia']}'>{$row['descripcion']}</option>";
                                        }
                                        ?>
                                    </select><br>
                                </div>

                                <div class="col-6">
                                    <label class="form-label" for="IdEstado">Estado de la Licencia:</label>
                                    <select class="form-select" id="IdEstado" name="IdEstado" required>
                                        <?php
                                        $stmt = $pdo->query("SELECT idestadoLicencia, nombreEstado FROM estadolicencia");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$row['idestadoLicencia']}'>{$row['nombreEstado']}</option>";
                                        }
                                        ?>
                                    </select><br><br>
                                </div>
                                <!-- Detalles de Licencia -->
                                <h2>Detalles de la Licencia</h2>
                                <div id="detallesContainer">
                                    <!-- Detalle inicial -->
                                    <div class="detalle">
                                        <div class="col-6">
                                            <label class="form-control" for="detalles_descripcion[]">Descripción del Detalle:</label>
                                            <input class="form-control" type="text" id="detalles_descripcion[]"  name="detalles_descripcion[]" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-control" for="detalles_documentacion[]">Documentación:</label>
                                            <input class="form-control" type="file" id="detalles_documentacion[]" name="detalles_documentacion[]" accept="application/pdf">
                                        </div>

                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-secondary" onclick="agregarDetalle()">Agregar Otro Detalle</button><br><br>
                                    <button class="btn btn-primary" name='BotonRegistrar' type="submit">Registrar Licencia</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <?php include_once 'partes/footer.php'; ?>
        <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <!-- Vendor JS Files
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script> -->
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>-->
        <script src="assets/vendor/tinymce/tinymce.min.js"></script>
        <script src="assets/js/cartel.js"></script>
        <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->

        <!-- Template Main JS File -->
        <script src="assets/js/main.js"></script>
</body>

</html>