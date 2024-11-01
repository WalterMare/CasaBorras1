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

require_once 'select_relacion.php';
$listadoRelacion = Listar_relacion($conexion);
$CantidadRelacion = count($listadoRelacion);

require_once 'select_familiar.php';
if (!empty($_GET['ID'])) {
  $listadoFamiliar = Ver_familiar($conexion, $_GET['ID']);
} else {
  $_SESSION['Mensaje'] = 'Sin datos para mostrar...';
}
require_once 'validacion_registro_familiar.php';

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
      <h1>Modificar datos de un Familiar</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de empleados</li>
          <li class="breadcrumb-item">Ver un empleado</li>
          <li class="breadcrumb-item active">Modificar Familiar</li>
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
              if (!empty($_POST['BotonRegistrar'])) {
                //estoy en condiciones de poder validar los datos
                $Mensaje = Validar_Datos_Familiar_bis();
                if (empty($Mensaje)) {
                  if (ModificarFamiliar($conexion, $_GET['ID'], $_GET['IDEMPLEADO']) != false) {
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

              <form class="row g-3" method="post"> <!--se agrego el metodo post para la captura de datos -->

                <div class="col-6">
                  <label for="nombre" class="form-label">Nombre (*)</label>
                  <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $listadoFamiliar['NOMBRE']; ?>">
                </div>


                <div class="col-6">
                  <label for="apellido" class="form-label">Apellido (*)</label>
                  <input type="text" class="form-control" id="apellido" name='apellido' value="<?php echo $listadoFamiliar['APELLIDO']; ?>">
                </div>

                <div class="col-6">
                  <label for="dni" class="form-label">Documento</label>
                  <input type="number" class="form-control" id="dni" name="dni" value="<?php echo $listadoFamiliar['DOCUMENTO']; ?>">
                </div>



                <div class="col-6">
                  <label for="tel" class="form-label">Tel</label>
                  <input type="number" class="form-control" id="tel" name="tel" value="<?php echo $listadoFamiliar['TEL']; ?>">
                </div>

                <div class="col-6">
                  <label for="fechanacimiento" class="form-label">Fecha Nacimiento (*)</label>
                  <input type="date" class="form-control" id="fechanacimiento" name="fechanacimiento" value="<?php echo date('Y-m-d', strtotime($listadoFamiliar['FECHANACIMIENTO'])); ?>">
                </div>


                <div class="col-6">
                  <label name="selector" for="selector" class="form-label">Relación</label>
                  <select class="form-select" aria-label="Selector" id="selector" name="relacion"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                    <option value="<?php echo $listadoFamiliar['IDRELACION']; ?>"><?php echo $listadoFamiliar['RELACION']; ?></option>
                    <?php
                    $selected = '';
                    for ($i = 0; $i < $CantidadRelacion; $i++) {
                      if (!empty($_POST['relacion']) && $_POST['relacion'] ==  $listadoRelacion[$i]['ID']) { //recuerda el elemento seleccionado
                        $selected = 'selected';
                      } else {
                        $selected = ''; //limpia la variable para que solo se seleccione una opcion
                      }
                    ?>
                      <option value="<?php echo $listadoRelacion[$i]['ID']; ?>" <?php echo $selected; ?>>
                        <?php echo $listadoRelacion[$i]['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>


                <div class="text-center">
                  <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar" >Guardar</button>
        
                  <a href="Listado_empleados.php" class="text-primary fw-bold">Volver al listado</a>
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