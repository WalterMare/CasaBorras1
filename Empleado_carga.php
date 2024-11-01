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

require_once 'select_provincia.php';
$listadoProvincia = Listar_provincia($conexion);
$CantidadProvincias = count($listadoProvincia);

require_once 'select_estadocivil.php';
$listadoEstadocivil = Listar_Estadocivil($conexion);
$CantidadEstadoCivil = count($listadoEstadocivil);

require_once 'select_sexo.php';
$listadoSexo = Listar_sexo($conexion);
$CantidadSexo = count($listadoSexo);

require_once 'select_tipo.php';
$listadoTipo = Listar_tipo($conexion);
$CantidadTipo = count($listadoTipo);

require_once 'select_cargo.php';
$listadoCargo = Listar_cargo($conexion);
$CantidadCargo = count($listadoCargo);


require_once 'validacion_registro_empleado.php';
require_once 'insertar_Empleado.php';


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
      <h1>Registrar un nuevo Empleado</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de empleados</li>
          <li class="breadcrumb-item active">Carga nuevo Empleado</li>
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
                $Mensaje = Validar_Datos();
                if (empty($Mensaje)) {
                  if (InsertarEmpleado($conexion) != false) {
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




              <form class="row g-3" method="post" enctype="multipart/form-data"> <!--se agrego el metodo post para la captura de datos -->

                <div class="col-6">
                  <label for="nombre" class="form-label">Nombre (*)</label>
                  <input type="text" class="form-control" id="nombre" name="nombre">
                </div>


                <div class="col-6">
                  <label for="apellido" class="form-label">Apellido (*)</label>
                  <input type="text" class="form-control" id="apellido" name='apellido'>
                </div>

                <div class="col-6">
                  <label for="documento" class="form-label">Documento</label>
                  <input type="number" class="form-control" id="documento" name="documento">
                </div>
                <div class="col-6">
                  <label for="direccion" class="form-label">Direccion(*)</label>
                  <input type="text" class="form-control" id="direccion" name='direccion'>
                </div>

                <div class="col-6">
                  <label for="ciudad" class="form-label">Ciudad</label>
                  <input type="text" class="form-control" id="ciudad" name="ciudad">
                </div>

                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Provincia (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="provincia"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadProvincias; $i++) {
                      if (!empty($_POST['provincia']) && $_POST['provincia'] ==  $listadoProvincia[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoProvincia[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoProvincia[$i]['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>


                <div class="col-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="text" class="form-control" id="email" name="email">
                </div>

                <div class="col-6">
                  <label for="tel" class="form-label">Tel</label>
                  <input type="number" class="form-control" id="tel" name="tel">
                </div>

                <div class="col-6">
                  <label for="fechanacimiento" class="form-label">Fecha Nacimiento (*)</label>
                  <input type="date" class="form-control" id="fechanacimiento" name="fechanacimiento">
                </div>

                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Estado Civil (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="estadocivil"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadEstadoCivil; $i++) {
                      if (!empty($_POST['estadocivil']) && $_POST['estadocivil'] ==  $listadoEstadocivil[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoEstadocivil[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoEstadocivil[$i]['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Sexo (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="sexo"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadSexo; $i++) {
                      if (!empty($_POST['sexo']) && $_POST['sexo'] ==  $listadoSexo[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoSexo[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoSexo[$i]['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-6">
                  <label for="fechainicio" class="form-label">Fecha Inicio (*)</label>
                  <input type="date" class="form-control" id="fechainicio" name="fechainicio">
                </div>



                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Cargo (*)</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="cargo"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="">Selecciona una opcion</option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadCargo; $i++) {
                      if (!empty($_POST['cargo']) && $_POST['cargo'] ==  $listadoCargo[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoCargo[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoCargo[$i]['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

              

                <div class="col-6">
                  <label class="form-label">Estado</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck1" name="estado" value='1' <?php echo (!empty($_POST['estado']) && $_POST['estado'] == '1') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gridCheck1"> Activo</label>
                  </div>
                </div>
                <div class="col-6">
                  <label for="imagen" class="form-label">Seleccione una imagen (*)</label>
                  <input type="file" class="form-control" id="imagen" name="imagen" accept=".jpg,.png" required>
                </div>
                <div class="col-6">
                  <img width= '80px' height='80px'  src='assets/img/user.png' id="img" class="rounded-circle" >
                </div>



                <div class="text-center">
                  <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar">Registrar</button>
                  <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
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
  <script src="assets/js/imagen.js"></script>



</body>

</html>