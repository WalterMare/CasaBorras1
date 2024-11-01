<?php 
session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre']) ) {
  header('Location: cerrarsesion.php');
  exit;}

require_once 'conexiondb.php';
$conexion = ConexionBD();

include_once 'select_usuario.php';
$listadoUsuarios = Listar_Usuario($conexion);
$CantidadUsuarios = count($listadoUsuarios);

include_once 'select_transporte_marca.php';
$listadoTransporte = Listar_Transporte($conexion);
$CantidadTransporte = count($listadoTransporte);

include_once 'select_destino.php';
$listadoDestino = Listar_Destino($conexion);
$CantidadDestino = count($listadoDestino);

include_once 'validacion_registro_viaje.php';
include_once 'insertar_viaje.php';
?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>2do Desempeño</title>
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
  <?php include_once 'partes/header.php'?>
  <!-- End Header -->
  <!-- ======= Sidebar ======= -->
  <?php include_once 'partes/menu.php';?>
  <!-- End Sidebar-->
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Registrar un nuevo viaje</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Viajes</li>
          <li class="breadcrumb-item active">Carga</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Ingresa los datos</h5>

              <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-1"></i>
                Los campos indicados con (*) son requeridos
              </div>

              <?php
              $Mensaje = '';
              $Estilo = 'warning';
              if (!empty($_POST['BotonRegistrar'])) {
                //estoy en condiciones de poder validar los datos
                $Mensaje = Validar_Datos_viaje();
                if (empty($Mensaje)) {
                  if (InsertarViaje($conexion) != false) {
                    $Mensaje = 'Se ha registrado correctamente.';
                    $_POST = array();
                    $Estilo = 'success';
                  } ?>
                 <div class="alert alert-success alert-dismissible fade show" role="alert">
                 <i class="bi bi-check-circle me-1"></i>
                    <?php echo $Mensaje; ?>
                  </div>
                <?php  } else { ?>
                  <div class="alert alert-warning alert-dismissible fade show" role="alert">
                  <i class="bi bi-exclamation-triangle me-1"></i>
                    <?php echo $Mensaje; ?>
                  </div><?php }
                    } ?>

                    

              <form class="row g-3" method="post">
                <div class="col-12">
                  <label for="selector" class="form-label">Chofer (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="usuario">
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadUsuarios; $i++) {
                      if (!empty($_POST['usuario']) && $_POST['usuario'] ==  $listadoUsuarios[$i]['ID']) {
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoUsuarios[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoUsuarios[$i]['Apellido']." ".$listadoUsuarios[$i]['Nombre']." ".$listadoUsuarios[$i]['DNI']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-12">
                  <label for="selector" class="form-label">Transporte (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="transporte">
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadTransporte; $i++) {
                      if (!empty($_POST['transporte']) && $_POST['transporte'] ==  $listadoTransporte[$i]['ID']) {
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoTransporte[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoTransporte[$i]['Denominacion']."-".$listadoTransporte[$i]['Modelo']."-".$listadoTransporte[$i]['Patente']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-12">
                  <label for="fecha" class="form-label">Fecha programada (*)</label>
                  <input type="date" class="form-control" id="fecha" name="fecha">
                </div>
                <div class="col-12">
                  <label for="selector" class="form-label">Destino (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="destino">
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadDestino; $i++) {
                      if (!empty($_POST['destino']) && $_POST['destino'] ==  $listadoDestino[$i]['ID']) {
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoDestino[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoDestino[$i]['Nombre']; ?>
                      </option>
                    <?php } ?>

                  </select>
                </div>
                <div class="col-12">
                  <label for="costo" class="form-label">Costo (*)</label>
                  <input type="number" class="form-control" id="costo" name="costo">
                </div>
                <div class="col-12">
                  <label for="porc" class="form-label">Porcentaje chofer (*)</label>
                  <input type="number" class="form-control" id="porc" name="porcentaje" max="100">
                </div>
                <div>
                  <input type="hidden" name="usuarioregistro" value="<?php echo $_SESSION['Usuario_Id'];?>">
                </div>
                <div class="text-center">
                  <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar" >Registrar</button>
                  <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
                  <a href="index.php" class="text-primary fw-bold">Volver al index</a>
                </div>
          
              </form><!-- Vertical Form -->

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->


  <!-- ======= Footer ======= -->
  <?php include_once 'partes/footer.php'?>
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