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

require_once 'select_empleado.php';
$listadoEmpleado = Listar_empleado($conexion);
$CantidadEmpleado = count($listadoEmpleado);

require_once 'select_embargo.php';
if (!empty($_GET['ID'])) {
  $datosObtenidos = ObtenerEmbargoPorID($conexion, $_GET['ID']);
} else {
  $_SESSION['Mensaje'] = 'Sin datos para mostrar...';
}

require_once 'validacion_registro_Embargo.php';
require_once 'Modificar_Embargo.php';



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
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Modificar Embargo Judicial</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de movimientos</li>
          <li class="breadcrumb-item active">Modificar Embargo Judicial</li>
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
                  if (ModificarEmbargo($conexion, $_GET['ID']) != false) {
                    $Mensaje = 'Se ha registrado correctamente.';
                    $_POST = array();
                    $Estilo = 'success';
                  } ?>
                  <div id='cartel' class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $Mensaje; ?>
                  </div>
                <?php  } else { ?>
                  <div id='cartel' class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?php echo $Mensaje; ?>
                  </div><?php }
                    } ?>


              <form class="row g-3" method="post">

                <!-- Empleado -->
                <div class="col-6">
                  <label for="selector" class="form-label">Empleado(*)</label>
                  <select class="form-select" id="selector" name="empleado" required>
                    <option value="">Selecciona una opción</option>
                    <?php
                    for ($i = 0; $i < $CantidadEmpleado; $i++) {
                      $selected = ($listadoEmpleado[$i]['ID'] == $datosObtenidos['IDEMPLEADO']) ? 'selected' : '';
                      echo '<option value="' . $listadoEmpleado[$i]['ID'] . '" ' . $selected . '>' .
                        $listadoEmpleado[$i]['NOMBRE'] . ' ' . $listadoEmpleado[$i]['APELLIDO'] . '</option>';
                    }
                    ?>
                  </select>
                </div>

                <!-- Fecha de Registro -->
                <div class="col-6">
                  <label for="fecha" class="form-label">Fecha de Registro(*)</label>
                  <input type="date" class="form-control" id="fecha" name="fecha" required
                    value="<?php echo $datosObtenidos['FECHA']; ?>">
                </div>

                <!-- Expediente -->
                <div class="col-6">
                  <label for="expediente" class="form-label">Nro. Expediente(*)</label>
                  <input type="text" class="form-control" id="expediente" name="expediente" required
                    value="<?php echo $datosObtenidos['EXPEDIENTE']; ?>">
                </div>

                <!-- Tipo de Embargo -->
                <div class="col-6">
                  <label for="tipo" class="form-label">Tipo de Embargo(*)</label>
                  <select class="form-select" id="tipo" name="tipo" required>
                    <option value="">Selecciona...</option>
                    <?php
                    $tipos = ['Alimentos', 'Bancario', 'AFIP', 'Otro'];
                    foreach ($tipos as $tipo) {
                      $selected = ($datosObtenidos['TIPO'] == $tipo) ? 'selected' : '';
                      echo '<option value="' . $tipo . '" ' . $selected . '>' . $tipo . '</option>';
                    }
                    ?>
                  </select>
                </div>

                <!-- Fechas de Vigencia -->
                <div class="col-6">
                  <label for="fecha_inicio" class="form-label">Fecha Inicio(*)</label>
                  <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required
                    value="<?php echo $datosObtenidos['FECHA_INICIO']; ?>">
                </div>

                <div class="col-6">
                  <label for="fecha_fin" class="form-label">Fecha Fin</label>
                  <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                    value="<?php echo $datosObtenidos['FECHA_FIN']; ?>">
                  <small class="text-muted">Dejar vacío si es indefinido</small>
                </div>

                <!-- Monto fijo -->
                <div class="col-6">
                  <label for="monto" class="form-label">Monto Fijo Mensual</label>
                  <input type="number" step="0.01" class="form-control" id="monto" name="monto"
                    value="<?php echo $datosObtenidos['MONTO']; ?>">
                </div>

                <!-- Porcentaje -->
                <div class="col-6">
                  <label for="porcentaje" class="form-label">% del Sueldo</label>
                  <input type="number" step="0.01" class="form-control" id="porcentaje" name="porcentaje"
                    value="<?php echo $datosObtenidos['PORCENTAJE']; ?>">
                  <small class="text-muted">Ejemplo: 20 = 20%</small>
                </div>

                <!-- Estado -->
                <div class="col-6">
                  <label for="estado" class="form-label">Estado</label>
                  <select class="form-select" id="estado" name="estado">
                    <option value="1" <?php echo ($datosObtenidos['ESTADO'] == 1) ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo ($datosObtenidos['ESTADO'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                  </select>
                </div>

                <!-- Descripción -->
                <div class="col-12">
                  <label for="descripcion" class="form-label">Descripción(*)</label>
                  <input type="text" class="form-control" id="descripcion" name="descripcion" required
                    value="<?php echo $datosObtenidos['DESCRIPCION']; ?>">
                </div>


                <div class="text-center">
                  <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar">Registrar</button>
                  <a href="Embargos.php" class="text-primary fw-bold">Volver al panel</a>
                </div>
              </form>
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
  <script>
    const monto = document.getElementById('monto');
    const porcentaje = document.getElementById('porcentaje');

    monto.addEventListener('input', () => {
      // Si hay valor en monto, deshabilita porcentaje
      porcentaje.disabled = monto.value.trim() !== '';
    });

    porcentaje.addEventListener('input', () => {
      // Si hay valor en porcentaje, deshabilita monto
      monto.disabled = porcentaje.value.trim() !== '';
    });
  </script>
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