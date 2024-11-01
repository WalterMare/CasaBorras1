<?php

require_once 'conexiondb.php';
$conexion = ConexionBD();


session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre']) ) {
  header('Location: cerrarsesion.php');
  exit;}


  require_once 'select_empleado.php';
  $listadoEmpleado = Listar_empleado($conexion);
  $CantidadEmpleado = count($listadoEmpleado);

  require_once 'select_tipo.php';
  $listadoTipo = Listar_tipo($conexion);
  $CantidadTipo = count($listadoTipo);

  require_once 'validar_registro_usuario.php';
  require_once 'insertar_usuario.php';
 
?>


<!DOCTYPE html>
<html lang="en">

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
  <?php include_once 'partes/header.php'?>
  <!-- End Header -->
  <!-- ======= Sidebar ======= -->
  <?php include_once 'partes/menu.php';?>
  
  <!-- End Sidebar-->
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Usuarios</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de empleados</li>
          <li class="breadcrumb-item active">Usuario</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

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

              <?php
              $Mensaje = '';
              $Estilo = 'warning';
              if (!empty($_POST['BotonRegistrar'])) {
                //estoy en condiciones de poder validar los datos
                $Mensaje = Validar_Datos_Usuario();
                if (empty($Mensaje)) {
                  if (InsertarUsuario($conexion) != false) {
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

                <div class="col-6">
                  <label for="usuario" class="form-label">Usuario (*)</label>
                  <input type="text" class="form-control" id="usuario" name="usuario">
                </div>

                <div class="col-6">
                  <label for="pass" class="form-label">Clave (*)</label>
                  <input type="password" class="form-control" id="clave" name="clave">
                </div>

                <div class="col-6">
                  <label for="pass1" class="form-label">Clave (*)</label>
                  <input type="password" class="form-control" id="clave1" name="clave1">
                </div>

                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Empleado(*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="empleado"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadEmpleado; $i++) {
                      if (!empty($_POST['empleado']) && $_POST['empleado'] ==  $listadoEmpleado[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoEmpleado[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoEmpleado[$i]['NOMBRE']." ".$listadoEmpleado[$i]['APELLIDO']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Tipo (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="tipo"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadTipo; $i++) {
                      if (!empty($_POST['tipo']) && $_POST['tipo'] ==  $listadoTipo[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoTipo[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoTipo[$i]['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="text-center">
                  <button class="btn btn-primary" type="submit" value='Registrar' name="BotonRegistrar">Registrar</button>
                  <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
                  <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
                </div>
              </form>

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