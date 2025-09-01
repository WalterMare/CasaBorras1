<?php

session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();



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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">


</head>

<body>

    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php' ?>
    <!-- End Header -->
    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>

    <!-- End Sidebar-->
    <main id="main" class="main ">

        <div class="pagetitle">
            <h1>Anticipos</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de movimientos</li>
                    <li class="breadcrumb-item active">Anticipos</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->


        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Anticipos</h5>

                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="viaticoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="registrar-tab" data-bs-toggle="tab" data-bs-target="#registrar" type="button" role="tab">Registrar Anticipo</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="consultar-tab" data-bs-toggle="tab" data-bs-target="#consultar" type="button" role="tab">Consultar</button>
                    </li>
                </ul>

                <!-- Contenido de las Tabs -->
                <div class="tab-content pt-2" id="viaticoTabsContent">
                    <!-- Tab de Registro -->
                    <div class="tab-pane fade show active" id="registrar" role="tabpanel">
                        <?php include 'Registrar_Anticipo.php'; ?>
                    </div>

                    <!-- Tab de Historial -->
                    <div class="tab-pane fade" id="consultar" role="tabpanel">
                        <?php include 'Listado_Anticipo_Empleado.php'; ?>
                    </div>
                </div>

            </div>
        </div>


    </main><!-- End #main -->
    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php' ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendor/tinymce/tinymce.min.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Recuperar la pestaña activa de la última sesión
            let activeTab = localStorage.getItem("activeTab");
            if (activeTab) {
                let tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="${activeTab}"]`));
                tab.show();
            }

            // Guardar la pestaña activa al cambiar
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener("shown.bs.tab", function(event) {
                    let tabID = event.target.getAttribute("data-bs-target");
                    localStorage.setItem("activeTab", tabID);
                });
            });
        });
    </script>


</body>

</html>