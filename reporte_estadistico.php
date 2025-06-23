<?php
session_start();

// Validar sesión
if (empty($_SESSION['Usuario_Nombre']) || $_SESSION['Usuario_Id'] != 1) {
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
$total_empleados_con_licencia = 0;
$total_dias_periodo = 0;
$empleado_top = null;
$empleados_con_licencias = [];
$licencias_por_mes = [];

// Procesar formulario
if (isset($_POST['fechainicio']) && isset($_POST['fechafin'])) {
    $fechaInicio = $_POST['fechainicio'];
    $fechaFin = $_POST['fechafin'];

    if (strtotime($fechaInicio) > strtotime($fechaFin)) {
        $mensaje = 'Error: La fecha de inicio no puede ser mayor que la fecha de fin.';
    } else {
        $fecha1 = new DateTime($fechaInicio);
        $fecha2 = new DateTime($fechaFin);
        $intervalo = $fecha1->diff($fecha2);
        $total_dias_periodo = $intervalo->days + 1; // +1 para incluir ambos días


        $licencias_data = Consultar_licencias_para_reporte_estadistico($MiConexion, $fechaInicio, $fechaFin);
        $licencia_por_cargo = Consultar_licencias_por_cargo($MiConexion, $fechaInicio, $fechaFin);
        $total_empleados_con_licencia = ContarEmpleadosConLicencia($MiConexion, $fechaInicio, $fechaFin);
        $licencias_por_mes = LicenciasPorMes($MiConexion, $fechaInicio, $fechaFin);
        $empleado_top = EmpleadoConMasLicencias($MiConexion, $fechaInicio, $fechaFin);
        $empleados_con_licencias = EmpleadosConLicencias($MiConexion, $fechaInicio, $fechaFin);



        if (!$licencias_data && !$licencia_por_cargo) {
            $mensaje = "No se encontraron licencias en esas fechas.";
        }
    }
}

if (isset($_POST['generar_pdf'])) {
    $grafico_img_cargo = isset($_POST['grafico_img_cargo']) && is_string($_POST['grafico_img_cargo']) ? $_POST['grafico_img_cargo'] : null;

    $grafico_img_lineal = isset($_POST['grafico_img_lineal']) && is_string($_POST['grafico_img_lineal']) ? $_POST['grafico_img_lineal'] : null;

    generarReportePDF(
        $licencias_data,
        $licencia_por_cargo,
        $fechaInicio,
        $fechaFin,
        $grafico_img_cargo,
        $grafico_img_lineal,
        $empleados_con_licencias,
        $total_dias_periodo,
        $empleado_top,

    );
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
                                <div style="width: 800px; height: 400px;">
                                    <canvas id="graficoCargo" width="800" height="400"></canvas>
                                </div>

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
                                <div class="alert alert-info mt-4">
                                    <strong>Cantidad de empleados que tomaron al menos una licencia:</strong> <?= $total_empleados_con_licencia ?>
                                </div>
                                <?php if ($empleado_top): ?>
                                    <div class="alert alert-success mt-4">
                                        <strong>Empleado con más licencias:</strong>
                                        <?= $empleado_top['nombre'] . ' ' . $empleado_top['apellido'] ?>
                                        (<?= $empleado_top['total_licencias'] ?> licencias, <?= $empleado_top['total_dias'] ?> días)
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($empleados_con_licencias)): ?>
                                    <h5 class="card-title mt-5">Empleados que tomaron licencias</h5>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Apellido</th>
                                                <th>Cargo</th>
                                                <th>% Días de Licencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($empleados_con_licencias as $emp): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($emp['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($emp['apellido']) ?></td>
                                                    <td><?= htmlspecialchars($emp['cargo']) ?></td>
                                                    <td>
                                                        <?= round(($emp['total_dias'] / $total_dias_periodo) * 100, 2) ?>%
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            <?php endif; ?>


                            <div style="width: 800px; height: 400px;">
                                <canvas id="graficoLinealMeses" width="800" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const datos = <?= json_encode($licencia_por_cargo) ?>;

                    const labels = datos.map(item => `${item.cargo} - ${item.tipo_licencia}`);
                    const valores = datos.map(item => item.total_licencias);

                    function generarColores(n) {
                        const colores = [];
                        for (let i = 0; i < n; i++) {
                            const r = Math.floor(Math.random() * 200);
                            const g = Math.floor(Math.random() * 200);
                            const b = Math.floor(Math.random() * 200);
                            colores.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
                        }
                        return colores;
                    }

                    const coloresFondo = generarColores(labels.length);
                    const coloresBorde = coloresFondo.map(c => c.replace('0.7', '1'));

                    // Gráfico de barras
                    const ctxCargo = document.getElementById('graficoCargo').getContext('2d');
                    const chartCargo = new Chart(ctxCargo, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Licencias por Cargo y Tipo',
                                data: valores,
                                backgroundColor: coloresFondo,
                                borderColor: coloresBorde,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });

                    // Gráfico lineal
                    const ctxLineal = document.getElementById('graficoLinealMeses').getContext('2d');
                    const chartLineal = new Chart(ctxLineal, {
                        type: 'line',
                        data: {
                            labels: <?= json_encode(array_column($licencias_por_mes, 'mes')) ?>,
                            datasets: [{
                                label: 'Licencias por Mes',
                                data: <?= json_encode(array_column($licencias_por_mes, 'cantidad')) ?>,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1000,
                                onComplete: function() {}
                            }
                        }
                    });

                    // Exportar a PDF
                    document.getElementById('btnPdf').addEventListener('click', async function(e) {
                        e.preventDefault();
                        chartCargo.update();
                        chartLineal.update();
                        await new Promise(resolve => setTimeout(resolve, 1000));

                        const cargoImg = chartCargo.canvas.toDataURL('image/png', 1.0);
                        const linealImg = chartLineal.canvas.toDataURL('image/png', 1.0);

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.style.display = 'none';

                        const addField = (name, value) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            form.appendChild(input);
                        };

                        addField('fechainicio', document.getElementById('fechainicio').value);
                        addField('fechafin', document.getElementById('fechafin').value);
                        addField('generar_pdf', '1');
                        addField('grafico_img_cargo', cargoImg);
                        addField('grafico_img_lineal', linealImg);

                        document.body.appendChild(form);
                        form.submit();
                    });
                });
            </script>

        </section>
    </main>

    <?php include_once 'partes/footer.php'; ?>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>