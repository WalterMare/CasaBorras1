<?php

session_start();
unset($_SESSION['RadioSeleccionado']); //elimina los datos de la sesión elegida
unset($_SESSION['RadioSeleccionado2']);

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
$seleccionado = "1"; //se establece un valor por defecto para la primera carga de la página
$seleccionado2 = "";
if (isset($_POST['grupo'])) {
    $_SESSION['RadioSeleccionado'] = $_POST['grupo'];
    if (isset($_POST['grupo1'])) {
        $_SESSION['RadioSeleccionado2'] = $_POST['grupo1'];
    }
}

if (isset($_SESSION['RadioSeleccionado'])) {
    $seleccionado = $_SESSION['RadioSeleccionado'];
    if (isset($_SESSION['RadioSeleccionado2'])) {
        $seleccionado2 = $_SESSION['RadioSeleccionado2'];
    }
}

//voy a necesitar la conexion: incluyo la funcion de Conexion.
require_once 'conexiondb.php';

//genero una variable para usar mi conexion desde donde me haga falta
//no envio parametros porque ya los tiene definidos por defecto
$MiConexion = ConexionBD();


require_once 'select_UltimosEmpleados.php';


?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Listado de últimos empleados registrados</title>
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
            <h1>Listado de últimos empleados registrados</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de Reportes</li>
                    <li class="breadcrumb-item active">Listado últimos empleados registrados</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Filtros de búsqueda</h5>


                            <?php
                            $Mensaje = '';
                            $Estilo = 'warning';
                            $CantidadEmpleados = "";
                            if (!empty($_POST['BotonBuscar'])) {
                                //estoy en condiciones de poder validar los datos

                                if (Listar_ultimosEmpleados2($MiConexion, $_POST["grupo"] != false)) {
                                    if ($seleccionado2 == "") {
                                        $listadoEmpleados = Listar_ultimosEmpleados2($MiConexion, $_POST["grupo"]);
                                        $CantidadEmpleados = count($listadoEmpleados);
                                        $Mensaje = 'Datos encontrados.';
                                        $_POST = array();
                                        $Estilo = 'success';
                                    } else if ($seleccionado2 == "0" || $seleccionado2 == "1") {
                                        $listadoEmpleados = Listar_ultimosEmpleados($MiConexion, $_POST["grupo"], $_POST["grupo1"]);
                                        $CantidadEmpleados = count($listadoEmpleados);
                                        $Mensaje = 'Datos encontrados.';
                                        $_POST = array();
                                        $Estilo = 'success';
                
        
                                    }
                                } ?>
                                <div class="alert alert-<?php echo $Estilo; ?> alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?php if ($Mensaje == '') {
                                        $Mensaje = 'No existen empleados registrados en el período analizado';
                                        echo $Mensaje;
                                    } else {
                                        echo $Mensaje;
                                    } ?>
                                </div>
                            <?php } ?>


                            <form class="row g-6" method="post"> <!--se agrego el metodo post para la captura de datos -->

                                <div class="col-6">
                                    <label name="selector" for="selector" class="form-label">Lapso del tiempo de busqueda: </label>
                                    <input type="radio" id="opcion1" name="grupo" value="1" <?php if ($seleccionado == "1") echo "checked"; ?>>
                                    <label for="opcion1"> 1 año</label>
                                    <input type="radio" id="opcion2" name="grupo" value="3" <?php if ($seleccionado == "3") echo "checked"; ?>>
                                    <label for="opcion2"> 3 años</label>
                                    <input type="radio" id="opcion3" name="grupo" value="5" <?php if ($seleccionado == "5") echo "checked"; ?>>
                                    <label for="opcion3"> 5 años</label>
                                </div>
                                <div class="col-6">
                                    <label name="selector" for="selector" class="form-label">Estado en el que se encuentra: </label>
                                    <input type="radio" id="opcion4" name="grupo1" value="1" <?php if ($seleccionado2 == "1") echo "checked"; ?>>
                                    <label for="opcion4"> Activo</label>
                                    <input type="radio" id="opcion5" name="grupo1" value="0" <?php if ($seleccionado2 == "0") echo "checked"; ?>>
                                    <label for="opcion5"> Inactivo</label>
                                    <input type="radio" id="opcion5" name="grupo1" value="" <?php if ($seleccionado2 == "") echo "checked"; ?>>
                                    <label for="opcion5"> Ambos</label>

                                </div>

                                <div class="col-2 row g-6 text-center">
                                    <button class="btn btn-primary" type="submit" value="Buscar" name="BotonBuscar">Buscar</button>
                                </div>

                            </form><!-- Vertical Form -->

                            <?php if ($CantidadEmpleados != 0 && $CantidadEmpleados != null) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Listado de los últimos empleados registrados en el lapso de un año</h5>
                                                <!-- Default Table -->

                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Empleado</th>
                                                            <th scope="col">Fecha de Inicio</th>
                                                            <th scope="col">Estado</th>
                                                            <th scope="col">Cargo</th>
                                                        </tr>
                                                    </thead>

                                                    <?php if ($CantidadEmpleados != 0 && $CantidadEmpleados != null) { ?>
                                                        <tbody>
                                                            <?php for ($i = 0; $i < $CantidadEmpleados; $i++) { ?>
                                                                <tr>
                                                                    <th scope="row"><?php echo $i + 1; ?></th>
                                                                    <td><?php echo $listadoEmpleados[$i]['NOMBRE'] . " " . $listadoEmpleados[$i]['APELLIDO']; ?></td>
                                                                    <td><?php echo $listadoEmpleados[$i]['FECHA_INICIO']; ?></td>
                                                                    <td>
                                                                        <a><span class="badge bg-<?php echo $listadoEmpleados[$i]['ESTADO'] == 1 ? 'success' : 'danger'; ?>"><i class="bi bi-check-circle me-1"></i></span>
                                                                        </a>
                                                                    </td>
                                                                    <td><?php echo $listadoEmpleados[$i]['CARGO']; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    <?php } ?>
                                                </table>
                                                <div class="col-2 row g-6 text-center">
                                                    <button class="btn btn-primary" id="btnver">Generar Informe</button>
                                                </div>
                                                <!-- End Default Table Example -->

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }} ?>
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
    <script src="acciones_botones/btnGenerarInforme.js" ></script>
    <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>