<?php
session_start();

// Verificar si la sesión está activa
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
$Cantidad = 0; // Para el número de resultados obtenidos
$idLicencia = 0;
// Incluir la conexión a la base de datos
require_once 'conexiondb.php';
$conexion = ConexionBD();

// Obtener los parámetros de búsqueda desde el formulario
$empleado_id = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
$tipo_reporte = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : '';


require_once 'Select_reportes.php';
$listado = Listar_Reporte_Empleado3($conexion, $empleado_id, $tipo_reporte);
$Cantidad = count($listado);

$listadoEmbargo = Listar_Reporte_Empleado_Embargo($conexion, $empleado_id);
$CantidadEmbargo = count($listadoEmbargo);

$listadoDetalle = 0;

require_once 'select_empleado.php';
$empleado = Listar_empleadoId($conexion, $empleado_id);

require_once('TCPDF-main/tcpdf.php');


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Consulta de Licencias</title>
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
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1> Reportes</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Reporte Generado</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Reporte del Empleado:</h5>

                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Empleado</th>
                                        <th scope="col">D.N.I</th>
                                        <th scope="col">Sexo</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Inicio de Actividad</th>
                                        <th scope="col">Cargo</th>
                                        <th scope="col">Ciudad</th>
                                        <th scope="col">Provincia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($empleado)) { ?>
                                        <tr>
                                            <td><?php echo $empleado['NOMBRE'] . " " . $empleado['APELLIDO']; ?></td>
                                            <td><?php echo $empleado['DNI']; ?></td>
                                            <td><?php echo $empleado['SEXO']; ?></td>
                                            <td><?php echo $empleado['ESTADO']; ?></td>
                                            <td><?php echo $empleado['FECHAINICIO']; ?></td>
                                            <td><?php echo $empleado['CARGO']; ?></td>
                                            <td><?php echo $empleado['CIUDAD']; ?></td>
                                            <td><?php echo $empleado['PROVINCIA']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section> <!-- End Default Table Example --> <!-- Default Table -->
        <section class="section">
            <?php if ($Cantidad != 0 && $Cantidad != null && $tipo_reporte == '1') { ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Licencias con Detalles</h5>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Fecha Inicio</th>
                                            <th scope="col">Fecha Fin</th>
                                            <th scope="col">Cantidad de Días</th>
                                            <th scope="col">Tipo de Licencia</th>
                                            <th scope="col">Estado de Licencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 0; $i < $Cantidad; $i++) { ?>
                                            <tr>
                                                <th scope="row"><?php echo $i + 1; ?></th>
                                                <td><?php echo $listado[$i]['FECHAINICIO']; ?></td>
                                                <td><?php echo $listado[$i]['FECHAFIN']; ?></td>
                                                <td><?php echo $listado[$i]['CANTIDADDIAS']; ?></td>
                                                <td><?php echo $listado[$i]['TIPO']; ?></td>
                                                <td><?php echo $listado[$i]['ESTADO']; ?></td>
                                            </tr>
                                            <!-- Detalles de la Licencia -->
                                            <?php
                                            $idLicencia = $listado[$i]['ID'];
                                            $listadoDetalle = Listar_Reporte_Empleado3_Detalle($conexion, $idLicencia);
                                            ?>
                                            <tr>
                                                <td colspan="6">
                                                    <strong>Detalles para la Licencia ID <?php echo $idLicencia; ?>:</strong>
                                                    <?php if (!empty($listadoDetalle)) { ?>
                                                        <table class="table table-bordered mt-3">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">#</th>
                                                                    <th scope="col">Descripción</th>
                                                                    <th scope="col">Fecha de Creación</th>
                                                                    <th scope="col">Usuario Otorga</th>
                                                                    <th scope="col">Documentación</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($listadoDetalle as $j => $detalle) { ?>
                                                                    <tr>
                                                                        <th scope="row"><?php echo $j + 1; ?></th>
                                                                        <td><?php echo $detalle['DESCRIPCION']; ?></td>
                                                                        <td><?php echo $detalle['FECHACREACION']; ?></td>
                                                                        <td><?php echo $detalle['USUARIO']; ?></td>
                                                                        <td>
                                                                            <a href="download_document.php?file_id=<?php echo $idLicencia; ?>" class="btn btn-link">
                                                                                Descargar Documentación
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    <?php } else { ?>
                                                        <p class="text-muted">No hay detalles disponibles para esta licencia.</p>
                                                    <?php } ?>
                                                </td>

                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>
                                <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                    <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                    <input type="hidden" name="tipo_reporte" value="1"> <!-- Agregar el parámetro adicional -->
                                    <input type="hidden" name="id_licencia" value="<?php echo $idLicencia; ?>">
                                    <button type="submit" class="btn btn-primary" <?php echo $listadoDetalle == 0 ? 'disabled' : ''; ?>>Generar PDF</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else if ($Cantidad == 0 && $Cantidad == null && $tipo_reporte == '1') { ?>
                <div class="alert alert-warning">No hay licencias disponibles para este empleado.</div>
            <?php } ?>
        </section>
        <?php
        if ($tipo_reporte == '6') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Embargos</h5>
                                <?php if ($CantidadEmbargo != 0 && $CantidadEmbargo != null) { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Fecha</th>
                                                <th scope="col">Monto</th>
                                                <th scope="col">Descripción</th>
                                            </tr>
                                        </thead>

                                        <?php if ($CantidadEmbargo != 0 && $CantidadEmbargo != null) { ?>
                                            <tbody>
                                                <?php for ($i = 0; $i < $CantidadEmbargo; $i++) { ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $i + 1; ?></th>
                                                        <td><?php echo $listadoEmbargo[$i]['FECHA']; ?></td>
                                                        <td><?php echo $listadoEmbargo[$i]['MONTO']; ?></td>
                                                        <td><?php echo $listadoEmbargo[$i]['DESCRIPCION']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                <?php } else {
                                    echo "No se encontraron registros";
                                } ?> <!-- End Default Table Example -->
                                <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                    <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                    <input type="hidden" name="tipo_reporte" value="6">

                                    <button type="submit" class="btn btn-primary">Generar PDF</button>



                                </form>
                            </div>
                        </div>
                    </div>
                </div>


            </section>
        <?php } ?>
        <br>
        <a href="Reportes.php" class="text-primary fw-bold">Regresar</a>


    </main>

    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>

</body>

</html>