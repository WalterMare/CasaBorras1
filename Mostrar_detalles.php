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


// Suponiendo que tienes el ID de la licencia
$licencia = $_GET['ID']; // Este valor puede ser dinámico dependiendo de tu aplicación

// Generar la ruta del archivo de forma dinámica
$tipo_archivo = 'pdf';
switch ($tipo_archivo) {
  case 'pdf':
      $file_path = 'assets/archivos/licencia_' . $licencia . '.pdf';
      break;
  case 'jpg':
      $file_path = 'assets/archivos/licencia_' . $licencia . '.jpg';
      break;
  case 'png':
      $file_path = 'assets/archivos/licencia_' . $licencia . '.png';
      break;
  default:
      $file_path = 'assets/archivos/licencia_' . $licencia . '.dat'; // Para otros tipos no especificados
      break;
}


require_once 'select_tipoLicencia.php';
if (!empty($_GET['ID'])){
    $datoslicencia= Listar_Detalle_Licencia($conexion, $_GET['ID']);
}else{
    $_SESSION['Mensaje']= 'Sin datos para mostrar...';
}

require_once 'Obtener_Blob.php';
if (!empty($_GET['ID'])){
    $datodocumento= obtenerBlob($conexion, $_GET['ID']);
}else{
    $_SESSION['Mensaje']= 'Sin datos para mostrar...';
}
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

  <!-- ======= Header ======= -->
  <?php include_once 'partes/header.php' ?>
  <!-- End Header -->
  <!-- ======= Sidebar ======= -->
  <?php include_once 'partes/menu.php'; ?>

  <!-- End Sidebar-->
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Detalle Licencia</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de empleados</li>
          <li class="breadcrumb-item active">Detalle Licencia</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Datos</h5>
              
              <form class="row g-3" method="post" > <!--se agrego el metodo post para la captura de datos -->

                <div class="col-12">
                  <label for="documentacion" class="form-label">Documentacion</label>
                  <iframe class="form-control" width="100%" height="300px" src="<?php echo $file_path; ?>"></iframe>
                </div>

                <div class="col-4">
                  <label for="descripcion" class="form-label">Descripción</label>
                  <input type="text" class="form-control" id="descripcion" name='descripcion' value="<?php echo $datoslicencia['DESCRIPCION'];?>" disabled>
                </div>

                <div class="col-4">
                  <label for="usuario" class="form-label">Usuario que consedió</label>
                  <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo $datoslicencia['USUARIO'];?>" disabled>
                </div>

                <div class="col-4">
                  <label for="fechacreacion" class="form-label">Fecha Creación </label>
                  <input type="text" class="form-control" id="fechacreacion" name="fechacreacion" value="<?php echo $datoslicencia['FECHACREACION'];?>" disabled>
                </div>

                <div class="text-center">
                  <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
                </div>
              </form><!-- Vertical Form -->

            </div>
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

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>