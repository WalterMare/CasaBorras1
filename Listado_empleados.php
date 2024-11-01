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

require_once 'select_Trans_Dest_Usu_Via.php';
//voy a ir listando lo necesario para trabajar en este script: 
$ListadoReporte = Listar_Reporte_2($MiConexion, $_SESSION['Usuario_Nivel']);
$CantidadReportes = count($ListadoReporte);

//se incluye la funcion para calcular las fechas asi obtener el color que le corresponde a la tabla
//require_once 'testFechas.php';

?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Lista Empleados</title>
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
      <h1>Lista empleados registrados</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de personal</li>
          <li class="breadcrumb-item active">Listado de empleados</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Empleados cargados</h5>
              

              <!-- Default Table -->
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Id</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Ciudad</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Tel</th>
                    <th scope="col">Fecha Inicio</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0; $i < $CantidadReportes; $i++) { ?>
                    <tr>
                      <th scope="row"><?php echo $i + 1; ?></th>
                      <td><?php echo $ListadoReporte[$i]['ID']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['NOMBRE']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['APELLIDO']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['CIUDAD']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['PROVINCIA']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['TEL']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['FECHAINICIO']; ?></td>
                      <td>
                        <a onclick="if (confirm('Esta seguro que desea cambiar el Estado?')){return true;}else {return false;}"
                          href="cambiar_estado.php?ESTADO=<?php echo $ListadoReporte[$i]['ESTADO']; ?>&ID=<?php echo $ListadoReporte[$i]['ID']; ?> "
                          role='button' title='Modificar estado'><span class="badge bg-<?php echo $ListadoReporte[$i]['ESTADO'] == 1 ? 'success' : 'danger'; ?>"><i class="bi bi-check-circle me-1"></i></span>
                        </a>
                      </td>
                      <td><?php echo $ListadoReporte[$i]['CARGO']; ?></td>
                      <td>

                        <a href="Mostrar_datos.php?ID=<?php echo $ListadoReporte[$i]['ID']; ?>" role="button"" title=" Ver"<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i> </span> </a>
                        <a href="Modificar_datos.php?ID=<?php echo $ListadoReporte[$i]['ID']; ?>" role="button" title="Modificar" <span class="badge bg-info text-dark"><i class="bi bi-info-circle me-1"></i></span> </a>
                      </td>
                    </tr>
                  <?php }; ?>
                </tbody>
              </table>
              <!-- End Default Table Example -->
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