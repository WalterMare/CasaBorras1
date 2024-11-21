<?php
session_start();

// Si el elemento de sesión 'Usuario_Nombre' está vacío, redirigir al login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();  // Conexion a la base de datos

require_once 'select_empleado.php';
$listadoEmpleado = Listar_empleado($conexion);
$CantidadEmpleado = count($listadoEmpleado);

require_once 'select_reporte.php';
$listadoReporte = Listar_TipoReporte($conexion);
$Cantidadreporte = count($listadoReporte);

require_once 'validacion_registro_Reporte.php';
require_once 'Select_reportes.php';
require_once 'select_detalle_Reporte.php';

$Cantidad=0;
$Mensaje = '';
$Estilo = 'warning';

if (!empty($_POST['BotonFiltrar']) and !empty($_POST['empleado']) and empty($_POST['tipo']) ) {
    $listado = Listar_Reporte_Empleado($conexion,$_POST['empleado']);
    $Cantidad = count($listado);
} else {
    if (!empty($_POST['BotonFiltrar']) and !empty($_POST['empleado']) and !empty($_POST['tipo'])) {
        $listado = Listar_Licencia_Empleado2($conexion, $_POST['empleado']);
        $Cantidad = count($listado);
    }
    if (!empty($_POST['BotonFiltrar']) and empty($_POST['empleado']) and !empty($_POST['licencia']) and empty($_POST['fechainicio'])) {
        $listado = Listar_Licencia_Licencia($conexion, $_POST['licencia']);
        $Cantidad = count($listado);
    }
    if (!empty($_POST['BotonFiltrar']) and !empty($_POST['empleado']) and !empty($_POST['licencia']) and empty($_POST['fechainicio'])) {
        $listado = Listar_Licencia_Empleado_Licencia($conexion, $_POST['empleado'], $_POST['licencia']);
        $Cantidad = count($listado);
    }
} 
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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php'; ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>
    <!-- End Sidebar -->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Listar Reporte</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de reportes</li>
                    <li class="breadcrumb-item active">Listar Reportes</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ingresa los datos</h5>

                            <!-- Mensajes de éxito o advertencia -->
                            <?php if (!empty($Mensaje)): ?>
                                <div id='cartel' class="alert alert-<?= $Estilo ?> alert-dismissible fade show" role="alert">
                                    <i class="bi bi-<?= $Estilo == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-1"></i>
                                    <?= $Mensaje ?>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario de Registro -->
                            <form class="row g-3" method="post">
                                <div class="col-6">

                                    <label for="selector" class="form-label">Empleado(*)</label>
                                    <select class="form-select" id="selector" name="empleado" >
                                        <option value="">Selecciona una opción</option>
                                        <?php foreach ($listadoEmpleado as $empleado): ?>
                                            <option value="<?= $empleado['ID']; ?>" <?= (isset($_POST['empleado']) && $_POST['empleado'] == $empleado['ID']) ? 'selected' : ''; ?>>
                                                <?= $empleado['NOMBRE'] . " " . $empleado['APELLIDO']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label for="selector" class="form-label">Tipo de Reporte</label>
                                    <select class="form-select" id="tipoReporte" name="tipo" >
                                        <option value="">Selecciona una opción</option>
                                        <?php foreach ($listadoReporte as $reporte): ?>
                                            <option value="<?= $reporte['ID']; ?>" <?= (isset($_POST['tipo']) && $_POST['tipo'] == $reporte['ID']) ? 'selected' : ''; ?>>
                                                <?= $reporte['DESCRIPCION']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" value="Buscar" name="BotonFiltrar">Filtrar</button>
                            </form>
                                

                            <!-- Mostrar el detalle del reporte si es necesario -->
                            <?php if ($Cantidad != 0 && $Cantidad != null) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Detalle del Reporte</h5>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">IdReporte</th>
                                                            <th scope="col">Fecha</th>
                                                            <th scope="col">Tipo Reporte</th>
                                                            <th scope="col">Empleado</th>
                                                            <th scope="col">Per. Inicio</th>
                                                            <th scope="col">Per. Fin</th>
                                                            
                                                        </tr>
                                                    </thead>

                                                    <?php if ($Cantidad != 0 && $Cantidad != null) { ?>
                                                        <tbody>
                                                            <?php for ($i = 0; $i < $Cantidad; $i++) { ?>
                                                                <tr>
                                                                    <th scope="row"><?php echo $i + 1; ?></th>
                                                                    <td><?php echo $listado[$i]['ID']; ?></td>
                                                                    <td><?php echo $listado[$i]['FECHA']; ?></td>
                                                                    <td><?php echo $listado[$i]['IDREPORTE']; ?></td>
                                                                    <td><?php echo $listado[$i]['IDEMPLEADO']; ?></td>
                                                                    <td><?php echo $listado[$i]['PERIODOINICIO']; ?></td>
                                                                    <td><?php echo $listado[$i]['PERIODOFIN']; ?></td>
                                                                    <td>
                                                                        <a href="Registrar_detalleLicencia.php?ID=<?= $item['ID']; ?>" role="button" title="Agregar Detalle" class="badge bg-info text-black">
                                                                            <i class="bi bi-info-circle"></i> Agregar Detalle
                                                                        </a>
                                                                        <a href="Eliminar_Reporte.php?ID=<?php echo $ListadoReporte[$i]['ID']; ?>" role="button" title="Eliminar" class="badge bg-danger text-black">
                                                                        <i class="bi bi-exclamation-triangle"></i> Eliminar Reporte </a>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    <?php } ?>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>

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