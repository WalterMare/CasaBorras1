<?php

session_start();
$datosEmpleado=array(); 

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();

require_once 'select_familiar.php';
$ListadoReporte = Listar_familiares($conexion, $_GET['ID']);
$CantidadReportes = count($ListadoReporte);

require_once 'select_mostrardatos.php';
if (!empty($_GET['ID'])){
    $datosEmpleado=Listar_Empleado($conexion, $_GET['ID']);
}else{
    $_SESSION['Mensaje']= 'Sin datos para mostrar...';
}

require_once 'funcion_calcularEdad.php';


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
      <h1>Datos del Empleado</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de empleados</li>
          <li class="breadcrumb-item active">Datos del  Empleado</li>
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
                <div class="col-3">
                  <img width= '80px' height='80px'  src="data:image/jpg; base64,<?php echo base64_encode($datosEmpleado['IMAGEN']);?>" alt="" class="rounded-circle" >
                </div>
                <div class="col-3">
                  <label for="nombre" class="form-label">Nombre</label>
                  <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $datosEmpleado['NOMBRE'];?>" disabled>
                  
                </div>
               

                <div class="col-3">
                  <label for="apellido" class="form-label">Apellido</label>
                  <input type="text" class="form-control" id="apellido" name='apellido'value="<?php echo $datosEmpleado['APELLIDO'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="documento" class="form-label">Documento</label>
                  <input type="number" class="form-control" id="documento" name="documento"value="<?php echo $datosEmpleado['DNI'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="direccion" class="form-label">Direccion</label>
                  <input type="text" class="form-control" id="direccion" name='direccion'value="<?php echo $datosEmpleado['DIRECCION'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="ciudad" class="form-label">Ciudad</label>
                  <input type="text" class="form-control" id="ciudad" name="ciudad"value="<?php echo $datosEmpleado['CIUDAD'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="provincia" class="form-label">Provincia</label>
                  <input type="text" class="form-control" id="provincia" name="provincia"value="<?php echo $datosEmpleado['PROVINCIA'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="text" class="form-control" id="email" name="email"value="<?php echo $datosEmpleado['EMAIL'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="tel" class="form-label">Tel</label>
                  <input type="number" class="form-control" id="tel" name="tel"value="<?php echo $datosEmpleado['TEL'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="fechanacimiento" class="form-label">Fecha Nacimiento</label>
                  <input type="text" class="form-control" id="fechanacimiento" name="fechanacimiento"value="<?php echo $datosEmpleado['FECHANACIMIENTO'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="estadocivil" class="form-label">Estado Civil</label>
                  <input type="text" class="form-control" id="estadocivil" name="estadocivil"value="<?php echo $datosEmpleado['ESTADOCIVIL'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="sexo" class="form-label">Sexo</label>
                  <input type="text" class="form-control" id="sexo" name="sexo"value="<?php echo $datosEmpleado['SEXO'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="fechainicio" class="form-label">Fecha Inicio</label>
                  <input type="text" class="form-control" id="fechainicio" name="fechainicio"value="<?php echo $datosEmpleado['FECHAINICIO'];?>"disabled>
                </div>

                <div class="col-3">
                  <label for="cargo" class="form-label">Cargo</label>
                  <input type="text" class="form-control" id="cargo" name="cargo"value="<?php echo $datosEmpleado['CARGO'];?>"disabled>
                </div>

            

                <div class="col-3">
                  <label class="form-label">Estado</label>
                  <div class="form-check" > 
                    <input class="form-check-input" type="checkbox" id="gridCheck1" name="estado" value='1' <?php echo (!empty($datosEmpleado['ESTADO']) && $datosEmpleado['ESTADO'] == '1') ? 'checked' : ''; ?> disabled>
                    <label class="form-check-label" for="gridCheck1"> Activo</label>
                  </div>
                </div>
                <div class="col-12">
                  <label for="" class="form-label">FAMILIARES A CARGO</label>
                </div>

                <!-- Default Table -->
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Relación</th>
                    <th scope="col">Fecha Nacimiento</th>
                    <th scope="col">Edad</th>
                    <th scope="col">Tel</th> 
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0; $i < $CantidadReportes; $i++) { ?>
                    <tr>
                      <th scope="row"><?php echo $i + 1; ?></th>
                      <td><?php echo $ListadoReporte[$i]['NOMBRE']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['APELLIDO']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['DNI']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['RELACION']; ?></td>
                      <td><?php echo $ListadoReporte[$i]['FECHANACIMIENTO']; ?></td>
                      <td><?php echo edad($ListadoReporte[$i]['FECHANACIMIENTO'])." "."años";?></td>
                      <td><?php echo $ListadoReporte[$i]['TEL']; ?></td>
                      <td>
                        <a href="Mostrar_familiar.php?ID=<?php echo $ListadoReporte[$i]['ID'];?>&IDEMPLEADO=<?php echo $ListadoReporte[$i]['IDEMPLEADO']; ?>" role="button" title="Modificar" <span class="badge bg-info"><i class="bi bi-info-circle me-1"></i></span> </a>
                        <a onclick="if (confirm('Esta seguro que desea eliminar?')){return true;}else {return false;}"
                          href="eliminar_familiar.php?ID=<?php echo $ListadoReporte[$i]['ID'];?>"
                          role='button' title='Eliminar Familiar'><span class="badge bg-danger"><i class="bi bi-exclamation-octagon me-1"></i></span>
                        </a>
                        
                      </td>
                    </tr>
                  <?php }; ?>
                </tbody>
              </table>
              <!-- End Default Table Example -->

                

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