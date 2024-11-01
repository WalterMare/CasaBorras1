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

require_once 'Actualizar_Estado_Empleado.php';
actualizarEstados($conexion);




require_once 'select_empleado.php';
$ListadoReporte = Listar_empleado($conexion);
$CantidadEmpleados = count($ListadoReporte);

$ListadoReporte = Listar_empleado_activos($conexion);
$CantidadEmpleadosActivos = count($ListadoReporte);

$ListadoReporte = Listar_empleado_inactivos($conexion);
$CantidadEmpleadosInactivos = count($ListadoReporte);

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

  <?php include_once 'partes/header.php'; ?>
  <!-- ======= Sidebar ======= -->

  <?php include_once 'partes/menu.php'; ?>
  <!-- End Sidebar-->


  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Bienvenid@s!!</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active"><a href="index.php">Home</a></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Customers Card -->
            <div class="col-xxl-12 col-xl-12">

              <div class="card info-card customers-card">

                <div class="card-body">
                  <h5 class="card-title">Estas trabjando con el sistema de Casa Borras. <span>| Muchos exitos!</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="ps-3">
                      <img src="assets/img/img1.jpeg" alt="logo">
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Customers Card -->

            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-4">

              <div class="card info-card customers-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Total de Empleados <span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">

                      <h6><?php echo $CantidadEmpleados ?></h6>
                      <span class="text-danger small pt-1 fw-bold">100%</span> <span class="text-muted small pt-2 ps-1">de empleados de la empresa</span>

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->
            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-4">

              <div class="card info-card customers-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Empleados Activos <span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">

                      <h6><?php echo $CantidadEmpleadosActivos ?></h6>
                      <span class="text-danger small pt-1 fw-bold"><?php echo number_format((($CantidadEmpleadosActivos * 100) / $CantidadEmpleados), 2); ?> %
                      </span> <span class="text-muted small pt-2 ps-1">de empleados activos</span>

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->
            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-4">

              <div class="card info-card customers-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Empleados Inactivos<span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">

                      <h6><?php echo $CantidadEmpleadosInactivos ?></h6>
                      <span class="text-danger small pt-1 fw-bold"><?php echo number_format((($CantidadEmpleadosInactivos * 100) / $CantidadEmpleados), 2); ?> %</span> <span class="text-muted small pt-2 ps-1">de empleados inactivos</span>

                    </div>
                  </div>

                </div>

              </div>

            </div><!-- End Customers Card -->

          </div>
          
        </div><!-- End Left side columns -->

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

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>