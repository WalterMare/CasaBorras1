<?php
session_start();

// Validar sesión
if (empty($_SESSION['Usuario_Nombre'])) {
    echo "Sesión expirada. Redirigiendo al inicio de sesión...";
    header('Refresh: 3; URL=cerrarsesion.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once 'conexiondb.php';
require_once 'Reporte_Estadistico_licencia_pdf.php';  // Archivo con la función para generar el PDF
require_once 'generar_pdf.php';

// Conexión a la base de datos
$MiConexion = ConexionBD();

// Inicializar variables
$licencias_data = [];
$mensaje = '';
$fechaInicio = $fechaFin = '';

// Procesar formulario
if (isset($_POST['fechainicio']) && isset($_POST['fechafin'])) {
    $fechaInicio = $_POST['fechainicio'];
    $fechaFin = $_POST['fechafin'];

    // Validar rango de fechas
    if (strtotime($fechaInicio) > strtotime($fechaFin)) {
        $mensaje = 'Error: La fecha de inicio no puede ser mayor que la fecha de fin.';
    } else {
        // Consultar las licencias
        $licencias_data = Consultar_licencias_para_reporte_estadistico($MiConexion, $fechaInicio, $fechaFin);
        if (!$licencias_data) {
            $mensaje = "No se encontraron licencias en esas fechas.";
        }
    }
}

// Generar PDF si se solicita
if (isset($_POST['generar_pdf']) && $licencias_data) {
    generarReportePDF($licencias_data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Estadístico</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
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

<body class="container my-4">


    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php' ?>
    <!-- End Header -->
    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>


    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Reporte Estadístico</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de Reportes</li>
                    <li class="breadcrumb-item active">Generar Reporte Estadístico de Licencias</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Reporte Estadístico de Licencias</h5>

                            <form method="POST" class="row g-3 my-4">
                                <div class="col-md-6">
                                    <label for="fechainicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fechainicio" name="fechainicio" value="<?= htmlspecialchars($fechaInicio) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="fechafin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fechafin" name="fechafin" value="<?= htmlspecialchars($fechaFin) ?>" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Generar Reporte</button>
                                    <?php if ($licencias_data): ?>
                                        <button type="submit" name="generar_pdf" class="btn btn-success">Descargar PDF</button>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <?php if ($mensaje): ?>
                                <div class="alert alert-warning"><?= htmlspecialchars($mensaje) ?></div>
                            <?php endif; ?>

                            <?php if ($licencias_data): ?>
                                <canvas id="graficoBarras" class="my-4"></canvas>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tipo de Licencia</th>
                                            <th>Estado</th>
                                            <th>Total Licencias</th>
                                            <th>Total Días</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($licencias_data as $data): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($data['tipo_licencia']) ?></td>
                                                <td><?= htmlspecialchars($data['estado']) ?></td>
                                                <td><?= htmlspecialchars($data['total_licencias']) ?></td>
                                                <td><?= htmlspecialchars($data['total_dias']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                            <script>
                                <?php if ($licencias_data): ?>
                                    const data = {
                                        labels: <?= json_encode(array_unique(array_column($licencias_data, 'tipo_licencia'))) ?>,
                                        datasets: [
                                            <?php
                                            $estados = array_unique(array_column($licencias_data, 'estado'));
                                            foreach ($estados as $estado):
                                                $dataset = [];
                                                foreach ($licencias_data as $data) {
                                                    if ($data['estado'] === $estado) {
                                                        $dataset[] = $data['total_licencias'];
                                                    } else {
                                                        $dataset[] = 0;
                                                    }
                                                }
                                            ?> {
                                                    label: "<?= $estado ?>",
                                                    data: <?= json_encode($dataset) ?>,
                                                    backgroundColor: '<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>',
                                                    barThickness: 40, // Ajusta el ancho de las barras
                                                    maxBarThickness: 40, // Limita el ancho máximo si hay muchas barras
                                                },
                                            <?php endforeach; ?>
                                        ]
                                    };

                                    const config = {
                                        type: 'bar',
                                        data: data,
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'top'
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Licencias por Tipo y Estado'
                                                }
                                            },
                                            scales: {
                                                x: {
                                                    stacked: true, // Si estás agrupando por estado, activa apilado para que se vean mejor
                                                },
                                                y: {
                                                    beginAtZero: true,
                                                }
                                            }
                                        }
                                    };

                                    const graficoBarras = new Chart(
                                        document.getElementById('graficoBarras'),
                                        config
                                    );
                                <?php endif; ?>
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
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
    <script src="assets/js/cartel.js"></script>
    <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>