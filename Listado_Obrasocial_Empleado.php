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




require_once 'select_empleado.php';
$listadoEmpleado = Listar_empleado($MiConexion);
$CantidadEmpleado = count($listadoEmpleado);


require_once 'select_obrasocial.php';

$Cantidad = 0;
require_once 'Validad_Datos_Busqueda.php';

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Consulta Obra Social de un empleado</title>
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
            <h1>Obra Social asociada a un Empleado</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de movimientos</li>
                    <li class="breadcrumb-item active">Consultar Obra Social de un Empleado</li>
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
                            if (!empty($_POST['BotonBuscar'])) {
                                //estoy en condiciones de poder validar los datos
                                $Mensaje = Validar_Datos_busqueda();
                                if (empty($Mensaje)) {
                                    if (Listar_ObraSocialEmpleado($MiConexion, $_POST['empleado']) != false) {
                                        $listado = Listar_ObraSocialEmpleado($MiConexion, $_POST['empleado']);
                                        $Cantidad = count($listado);
                                        $Mensaje = 'Datos encontrados.';
                                        $_POST = array();
                                        $Estilo = 'success';
                                    } ?>
                                    <div class="alert alert-<?php echo $Estilo; ?> alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <?php if ($Mensaje == '') {
                                            $Mensaje = 'No tiene Obra Social asociada';
                                            echo $Mensaje;
                                        } else {
                                            echo $Mensaje;
                                        } ?>
                                    </div>
                            <?php }
                            } ?>



                            <form class="row g-6" method="post"> <!--se agrego el metodo post para la captura de datos -->

                                <div class="col-6">
                                    <label name="selector" for="selector" class="form-label">Listado de Empleados</label>
                                    <select class="form-select" aria-label="Selector" id="selector" name="empleado"> <!--combobox ya cargado con las marcas traidas desde la bd -->
                                        <option value="">Selecciona una opcion</option>
                                        <?php
                                        $selected = '';
                                        for ($i = 0; $i < $CantidadEmpleado; $i++) {
                                            if (!empty($_POST['empleado']) && $_POST['empleado'] ==  $listadoEmpleado[$i]['ID']) { //recuerda el elemento seleccionado
                                                $selected = 'selected';
                                            } else {
                                                $selected = ''; //limpia la variable para que solo se seleccione una opcion
                                            } ?>
                                            <option value="<?php echo $listadoEmpleado[$i]['ID']; ?>" <?php echo $selected; ?>>
                                                <?php echo $listadoEmpleado[$i]['NOMBRE'] . " " . $listadoEmpleado[$i]['APELLIDO']; ?>
                                            </option>
                                        <?php } ?>

                                    </select>

                                </div>
                                <div class="col-2 row g-3 text-center">
                                    <button class="btn btn-primary" type="submit" value="Buscar" name="BotonBuscar">Buscar</button>
                                </div>

                            </form><!-- Vertical Form -->
                            <?php if ($Cantidad != 0 && $Cantidad != null) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Obra Social del empleado</h5>
                                                <!-- Default Table -->

                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Empleado</th>
                                                            <th scope="col">Obra Social</th>
                                                            <th scope="col">Acciones</th>
                                                        </tr>
                                                    </thead>

                                                    <?php if ($Cantidad != 0 && $Cantidad != null) { ?>
                                                        <tbody>
                                                            <?php for ($i = 0; $i < $Cantidad; $i++) { ?>
                                                                <tr>
                                                                    <th scope="row"><?php echo $i + 1; ?></th>
                                                                    <td><?php echo $listado[$i]['NOMBRE'] . " " . $listado[$i]['APELLIDO']; ?></td>
                                                                    <td><?php echo $listado[$i]['DESCRIPCION']; ?></td>
                                                                    <td>
                                                                        <a href="Modificar_ObraSocial.php?ID=<?php echo $listado[$i]['ID']; ?>" role="button" title="Modificar" <span class="badge bg-info text-dark"><i class="bi bi-info-circle me-1"></i></span> </a>
                                                                    </td>
                                                                </tr>
                                                            <?php }; ?>
                                                        </tbody>
                                                    <?php } ?>

                                                </table>
                                                <!-- End Default Table Example -->

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
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