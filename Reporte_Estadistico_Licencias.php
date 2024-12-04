<?php

session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

//voy a necesitar la conexion: incluyo la funcion de Conexion.
require_once 'conexiondb.php';

//genero una variable para usar mi conexion desde donde me haga falta
//no envio parametros porque ya los tiene definidos por defecto
$MiConexion = ConexionBD();



?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Reporte Estadístico</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

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
</head>

<body>

    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php' ?>
    <!-- End Header -->
    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>
    <!-- End Sidebar-->
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Reporte Estadístico</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de Reportes</li>
                    <li class="breadcrumb-item active">Generar Reporte estadístico</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ingresa los datos</h5>


                            <?php
                            $Mensaje = '';
                            $Estilo = 'warning';
                            if (!empty($_POST['BotonBuscar'])) {
                                if (empty($Mensaje)) {
                                    require_once 'Reporte_Estadistico_licencia_pdf.php';
                                    if (Consultar_licencias_para_reporte_estadistico($MiConexion, $_POST['fechainicio'], $_POST['fechafin']) != false) {
                                        $listado =Consultar_licencias_para_reporte_estadistico($MiConexion, $_POST['fechainicio'], $_POST['fechafin']);
                                        $Mensaje = 'Datos encontrados.';
                                        $_POST = array();
                                        $Estilo = 'success';
                                    } ?>
                                    <div id='cartel' class="alert alert-<?php echo $Estilo; ?> alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <?php if ($Mensaje == '') {
                                            $Mensaje = 'No hay Licencias en esas fechas';
                                            echo $Mensaje;
                                        } else {
                                            echo $Mensaje;
                                        } ?>
                                    </div>
                            <?php }
                            } ?>

                            <form class="row g-6" method="post"> <!--se agrego el metodo post para la captura de datos -->

                                <div class="col-6">
                                    <label for="fechainicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fechainicio" name="fechainicio" required>
                                </div>
                                <div class="col-6">
                                    <label for="fechafin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fechafin" name="fechafin" required>
                                </div>
                                <div class="col-2 row g-3 text-center">
                                    <button class="btn btn-primary" type="submit" value="Buscar" name="BotonBuscar">Generar Reporte</button>
                                </div>

                            </form><!-- Vertical Form -->

                        </div>
                    </div>
                </div>

        </section>

    </main><!-- End #main -->


    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php' ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script> -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>-->
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>

    <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->
    <script src="assets/js/cartel.js"></script>
    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>