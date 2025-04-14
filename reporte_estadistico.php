<?php
session_start();

// Validar sesión
if (empty($_SESSION['Usuario_Nombre'])|| $_SESSION['Usuario_Id']!=1) {
    echo "Sesión expirada. Redirigiendo al inicio de sesión...";
    header('Refresh: 3; URL=cerrarsesion.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once 'conexiondb.php';
require_once 'Reporte_Estadistico_licencia_pdf.php';
require_once 'generar_pdf.php';

// Conexión a la base de datos
$MiConexion = ConexionBD();

// Inicializar variables
$licencias_data = [];
$licencia_por_cargo = [];
$mensaje = '';
$fechaInicio = $fechaFin = '';

// Procesar formulario
if (isset($_POST['fechainicio']) && isset($_POST['fechafin'])) {
    $fechaInicio = $_POST['fechainicio'];
    $fechaFin = $_POST['fechafin'];

    if (strtotime($fechaInicio) > strtotime($fechaFin)) {
        $mensaje = 'Error: La fecha de inicio no puede ser mayor que la fecha de fin.';
    } else {
        $licencias_data = Consultar_licencias_para_reporte_estadistico($MiConexion, $fechaInicio, $fechaFin);
        $licencia_por_cargo = Consultar_licencias_por_cargo($MiConexion, $fechaInicio, $fechaFin);
        if (!$licencias_data && !$licencia_por_cargo) {
            $mensaje = "No se encontraron licencias en esas fechas.";
        }
    }
}

if (isset($_POST['generar_pdf']) && $licencias_data && $licencia_por_cargo) {
    $grafico_img = $_POST['grafico_img'] ?? null;
    generarReportePDF($licencias_data, $licencia_por_cargo, $fechaInicio, $fechaFin, $grafico_img);
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

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="container my-4">
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Reporte Estadístico</h1>
        </div>
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
                                        <button type="submit" name="generar_pdf" id="btnPdf" class="btn btn-success"> Exportar a PDF</button>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <?php if ($mensaje): ?>
                                <div class="alert alert-warning"><?= htmlspecialchars($mensaje) ?></div>
                            <?php endif; ?>

                            <?php if (!empty($licencia_por_cargo)): ?>
                                <h5 class="card-title mt-5">Licencias por Cargo</h5>
                                <canvas id="graficoCargo" class="my-4"></canvas>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Cargo</th>
                                            <th>Tipo de Licencia</th>
                                            <th>Estado</th>
                                            <th>Total Licencias</th>
                                            <th>Total Días</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($licencia_por_cargo as $cargo): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cargo['cargo']) ?></td>
                                                <td><?= htmlspecialchars($cargo['tipo_licencia']) ?></td>
                                                <td><?= htmlspecialchars($cargo['estado']) ?></td>
                                                <td><?= htmlspecialchars($cargo['total_licencias']) ?></td>
                                                <td><?= htmlspecialchars($cargo['total_dias']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const labels = <?= json_encode(array_column($licencia_por_cargo, 'cargo')) ?>;
                                        const tiposLicencia = [...new Set(<?= json_encode(array_column($licencia_por_cargo, 'tipo_licencia')) ?>)];

                                        // ✅ Generar colores aleatorios
                                        function generarColorAleatorio() {
                                            const r = Math.floor(Math.random() * 256);
                                            const g = Math.floor(Math.random() * 256);
                                            const b = Math.floor(Math.random() * 256);
                                            return `rgb(${r}, ${g}, ${b})`;
                                        }

                                        const data = tiposLicencia.map(tipo => ({
                                            label: tipo || 'Sin especificar',
                                            data: <?= json_encode($licencia_por_cargo) ?>.map(c => c.tipo_licencia === tipo ? parseInt(c.total_licencias) : 0),
                                            backgroundColor: generarColorAleatorio(),
                                        }));

                                        // 📊 Crear el gráfico con ejes visibles
                                        const chart = new Chart(document.getElementById('graficoCargo'), {
                                            type: 'bar',
                                            data: {
                                                labels,
                                                datasets: data
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: true, // Ajusta mejor el tamaño
                                                plugins: {
                                                    legend: {
                                                        position: 'top'
                                                    },
                                                    title: {
                                                        display: true,
                                                        text: 'Licencias por Cargo'
                                                    }
                                                },
                                                scales: {
                                                    x: {
                                                        title: {
                                                            display: true,
                                                            text: 'Cargo',
                                                            color: '#000',
                                                            font: {
                                                                size: 14,
                                                                weight: 'bold'
                                                            }
                                                        },
                                                        ticks: {
                                                            color: '#000',
                                                            font: {
                                                                size: 12
                                                            }
                                                        },
                                                        grid: {
                                                            display: true,
                                                            color: '#e0e0e0'
                                                        }
                                                    },
                                                    y: {
                                                        title: {
                                                            display: true,
                                                            text: 'Cantidad de Licencias',
                                                            color: '#000',
                                                            font: {
                                                                size: 14,
                                                                weight: 'bold'
                                                            }
                                                        },
                                                        ticks: {
                                                            beginAtZero: true,
                                                            stepSize: 1,
                                                            color: '#000',
                                                            font: {
                                                                size: 12
                                                            }
                                                        },
                                                        grid: {
                                                            display: true,
                                                            color: '#e0e0e0'
                                                        }
                                                    }
                                                }
                                            }
                                        });

                                        // 🖼️ Captura del gráfico en base64 para PDF
                                        document.getElementById('btnPdf').addEventListener('click', function() {
                                            const imgData = chart.toBase64Image();
                                            const imgInput = document.createElement('input');
                                            imgInput.type = 'hidden';
                                            imgInput.name = 'grafico_img';
                                            imgInput.value = imgData;
                                            this.closest('form').appendChild(imgInput);
                                        });
                                    });
                                </script>





                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once 'partes/footer.php'; ?>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>