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

require_once 'select_reporte.php';
$listadoReporte = Listar_TipoReporte($conexion);
$Cantidadreporte = count($listadoReporte);


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
            <h1>Registrar Reporte</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de reportes</li>
                    <li class="breadcrumb-item active">Registrar Reportes</li>
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
                            if (isset($_POST['BotonRegistrar'])) {
                                // Obtener los datos del formulario
                                $idEmpleado = $_POST['empleado'];
                                $idTipoReporte = $_POST['tipo'];
                                $fecha = $_POST['fecha'];
                                $periodoInicio = $_POST['inicio'];
                                $periodoFin = $_POST['fin'];

                                // Insertar el reporte
                                $query = "INSERT INTO reporte (idreporte, fecha, idTipoReporte, idEmpleado, periodoInicio, periodoFin) 
              VALUES (null,'$fecha', '$idTipoReporte', '$idEmpleado',  '$periodoInicio', '$periodoFin')";
                                $result = mysqli_query($conexion, $query);

                                // Obtener el ID del reporte insertado
                                $idReporte = mysqli_insert_id($conexion);

                                // Insertar los detalles del reporte
                                if ($result) {
                                    // Recoger los detalles enviados por el formulario (puedes ajustar esta parte según cómo manejar los detalles)
                                    $detallesSeleccionados = $_POST['detalles']; // Array con los detalles seleccionados

                                    foreach ($detallesSeleccionados as $detalle) {
                                        $idDetalle = $detalle['id'];
                                        // Insertar el detalle en la tabla 'detallereporte'
                                        $queryDetalle = "INSERT INTO detallereporte (iddetalleReporte, idReporte, idLicencias, descripcionLicencia) 
                             VALUES ('$idReporte', '$idDetalle', '{$detalle['descripcion']}')";
                                        mysqli_query($conexion, $queryDetalle);
                                    }
                                }
                            }
                            ?>




                            <!-- Mensaje de Información -->
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-1"></i>
                                Los campos indicados con (*) son requeridos.
                            </div>

                            <!-- Formulario de Registro -->
                            <form class="row g-3" method="post">
                                <div class="col-6">
                                    <label for="selector" class="form-label">Empleado(*)</label>
                                    <select class="form-select" id="selector" name="empleado" required>
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
                                    <select class="form-select" id="tipoReporte" name="tipo" required>
                                        <option value="">Selecciona una opción</option>
                                        <?php foreach ($listadoReporte as $reporte): ?>
                                            <option value="<?= $reporte['ID']; ?>" <?= (isset($_POST['tipo']) && $_POST['tipo'] == $reporte['ID']) ? 'selected' : ''; ?>>
                                                <?= $reporte['DESCRIPCION']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Aquí añadimos la tabla que se llenará con los detalles -->
                                <div class="col-12 mt-4">
                                    <h5>Detalles del Reporte</h5>
                                    <table class="table table-bordered" id="tablaDetalles">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Descripción</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los detalles serán insertados aquí con AJAX -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-6">
                                    <label for="fecha" class="form-label">Fecha Reporte(*)</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                                </div>

                                <div class="col-6">
                                    <label for="inicio" class="form-label">Período de Inicio(*)</label>
                                    <input type="date" class="form-control" id="inicio" name="inicio" required>
                                </div>

                                <div class="col-6">
                                    <label for="fin" class="form-label">Período Fin(*)</label>
                                    <input type="date" class="form-control" id="fin" name="fin" required>
                                </div>

                                <div class="text-center">
                                    <button class="btn btn-primary" type="submit" name="BotonRegistrar">Registrar</button>
                                    <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
                                    <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
                                </div>
                            </form>

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
    <script src="assets/js/cartel.js"></script>
    <script src="assets/js/main.js"></script>


    <script>
        // Cuando se selecciona un tipo de reporte
        $('#tipoReporte').change(function() {
            var tipo = $(this).val(); // Obtener el tipo de reporte seleccionado

            // Realizar la solicitud AJAX para obtener los detalles
            $.ajax({
                url: 'Cargar_detalle.Reporte.php',
                type: 'POST',
                data: {
                    tipo: tipo
                },
                success: function(response) {
                    var detalles = JSON.parse(response);
                    var tableBody = $('#tablaDetalles tbody');
                    tableBody.empty(); // Limpiar la tabla antes de llenarla

                    // Rellenar la tabla con los detalles
                    detalles.forEach(function(detalle, index) {
                        var row = '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + detalle.descripcion + '</td>' +
                            '<td><button class="btn btn-success" data-id="' + detalle.id + '" data-descripcion="' + detalle.descripcion + '">Agregar al Reporte</button></td>' +
                            '</tr>';
                        tableBody.append(row);
                    });
                }
            });
        });
    </script>

</body>

</html>