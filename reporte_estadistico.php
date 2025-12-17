<?php
session_start();

// Validar sesión
if (empty($_SESSION['Usuario_Nombre'])) {
    echo "Sesión expirada. Redirigiendo al inicio de sesión...";
    header('Refresh: 3; URL=cerrarsesion.php');
    exit;
}
require_once 'seguridad.php';

if (!TieneAccesos(
    $_SESSION['Usuario_Nivel'],
    $_SESSION['Usuario_Roles_Funcionales'],
    ['Gerente de Departamento']
)) {
    include 'acceso_denegado.php';
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
$total_dias_ausentes = 0;
$total_dias_ausentes_pagos = 0;
$licencias_pago = [];

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
        $licencias_pago = LicenciasPago($MiConexion, $fechaInicio, $fechaFin);


        if (!$licencias_data && !$licencia_por_cargo) {
            $mensaje = "No se encontraron licencias en esas fechas.";
        }
    }
}

foreach ($licencias_data as $lic) {
    $total_dias_ausentes += $lic['total_dias'];

    // Ajustá según cómo guardás el tipo (ejemplo: "pago", "no pago")
    if (isset($lic['es_pago']) && $lic['es_pago'] == 1) {
        $total_dias_ausentes_pagos += $lic['total_dias'];
    }
}

$dias_trabajados = max(0, $total_dias_periodo - $total_dias_ausentes);
$dias_trabajados_pago = max(0, $total_dias_periodo - $total_dias_ausentes_pagos);

if (isset($_POST['generar_pdf'])) {

    $grafico_img_cargo = isset($_POST['grafico_img_cargo']) && is_string($_POST['grafico_img_cargo'])
        ? $_POST['grafico_img_cargo']
        : null;

    $grafico_img_lineal = isset($_POST['grafico_img_lineal']) && is_string($_POST['grafico_img_lineal'])
        ? $_POST['grafico_img_lineal']
        : null;

    $grafico_img_pago = isset($_POST['grafico_img_pago']) && is_string($_POST['grafico_img_pago'])
        ? $_POST['grafico_img_pago']
        : null;

    $empleado_top = $empleado_top ?? null; // si querés mostrar el top

    generarReportePDF(
        $grafico_img_cargo,
        $grafico_img_lineal,
        $grafico_img_pago,
        $_POST['fechainicio'] ?? '',
        $_POST['fechafin'] ?? '',
        $empleado_top
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
                            <h4 class="card-title mb-4">Reporte Estadístico de Licencias</h4>

                            <!-- Formulario de selección de fechas -->
                            <form method="POST" class="row g-3 mb-5">
                                <div class="col-md-4">
                                    <label for="fechainicio" class="form-label fw-semibold">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fechainicio" name="fechainicio" value="<?= htmlspecialchars($fechaInicio) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="fechafin" class="form-label fw-semibold">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fechafin" name="fechafin" value="<?= htmlspecialchars($fechaFin) ?>" required>
                                </div>
                                <div class="col-md-3 d-flex flex-column justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Generar Reporte</button>
                                </div>
                                <hr>
                            </form>

                            <!-- Gráficos en cards separadas -->
                            <div class="row g-4">

                                <!-- Ausentismo Total -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-light fw-semibold">Ausentismo Total</div>
                                        <div class="card-body d-flex justify-content-center">
                                            <canvas id="graficoTortaAusentismoPago" style="max-width: 100%; height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ausentismo Pago -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-light fw-semibold">Ausentismo Pago por Tipo</div>
                                        <div class="card-body d-flex justify-content-center">
                                            <canvas id="graficoTortaPago" style="max-width: 100%; height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Licencias por Mes -->
                                <div class="col-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-light fw-semibold">Licencias por Mes</div>
                                        <div class="card-body d-flex justify-content-center">
                                            <canvas id="graficoLinealMeses" style="width: 100%; height: 395px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Licencias por Cargo -->
                                <div class="col-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-light fw-semibold">Licencias por Cargo</div>
                                        <div class="card-body d-flex justify-content-center">
                                            <canvas id="graficoCargo" style="width: 100%; height: 200px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="text-center">
                                <?php if ($licencias_data): ?>
                                    <button type="submit" name="generar_pdf" id="btnPdf" class="btn btn-success">Exportar a PDF</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // === Función reutilizable para generar colores ===
                    function generarColores(n) {
                        const colores = [];
                        for (let i = 0; i < n; i++) {
                            const hue = (i * 360 / n) % 360;
                            colores.push(`hsl(${hue}, 70%, 50%)`);
                        }
                        return colores;
                    }

                    // === Datos desde PHP ===
                    const datosCargo = <?= json_encode($licencia_por_cargo, JSON_NUMERIC_CHECK) ?>;
                    const licenciasPorMes = <?= json_encode($licencias_por_mes, JSON_NUMERIC_CHECK) ?>;
                    const diasPeriodo = <?= json_encode($total_dias_periodo) ?>;
                    const diasAusentes = <?= json_encode($total_dias_ausentes) ?>;
                    const diasTrabajados = <?= json_encode($dias_trabajados) ?>;
                    const licenciasPago = <?= json_encode($licencias_pago, JSON_NUMERIC_CHECK) ?>;
                    const diasAusentesPago = <?= json_encode($total_dias_ausentes_pagos) ?>;
                    const diasTrabajadosPago = <?= json_encode($dias_trabajados_pago) ?>;

                    // -------------------------
                    // Gráfico torta por Cargo SOLO % 
                    // -------------------------
                    const cargos = [...new Set(datosCargo.map(d => d.cargo))];
                    const totalPorCargo = cargos.map(cargo => {
                        return datosCargo
                            .filter(d => d.cargo === cargo)
                            .reduce((sum, item) => sum + item.total_licencias, 0);
                    });
                    const coloresCargo = generarColores(cargos.length);

                    new Chart(document.getElementById('graficoCargo').getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: cargos,
                            datasets: [{
                                data: totalPorCargo,
                                backgroundColor: coloresCargo
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'right'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const dataset = context.dataset.data;
                                            const total = dataset.reduce((a, b) => a + b, 0);
                                            const value = context.raw;
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return percentage + '%';
                                        }
                                    }
                                },
                                datalabels: {
                                    formatter: (value, ctx) => {
                                        const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${percentage}%`; // ✅ Solo porcentaje en la torta
                                    },
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });

                    // -------------------------
                    // Gráfico lineal por mes
                    // -------------------------
                    new Chart(document.getElementById('graficoLinealMeses').getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: licenciasPorMes.map(d => d.mes),
                            datasets: [{
                                display: false,
                                label: 'Licencias por Mes',
                                data: licenciasPorMes.map(d => d.cantidad),
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: false
                                }, // ← apaga el título de Chart.js
                                legend: {
                                    display: false
                                } // ← opcional: oculta la leyenda ("Licencias por Mes")
                            },
                            layout: {
                                padding: {
                                    top: 8
                                }
                            } // ← opcional: un poco de aire arriba

                        }
                    });

                    new Chart(document.getElementById('graficoTortaAusentismoPago').getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: ['Días Trabajados', 'Días Ausentes'],
                            datasets: [{
                                data: [diasTrabajados, diasAusentes],
                                backgroundColor: ['#4CAF50', '#F44336']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = diasTrabajados + diasAusentes;
                                            const value = context.raw;
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return context.label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                },
                                datalabels: {
                                    formatter: (value, ctx) => {
                                        const total = ctx.chart.data.datasets[0].data
                                            .reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${percentage}%`; // 👈 solo porcentaje
                                    },
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels] // 👈 necesario
                    });

                    // -------------------------
                    // Gráfico torta - Licencias pago por tipo
                    // -------------------------
                    const labelsPago = licenciasPago.map(d => d.tipo_licencia);
                    const dataPago = licenciasPago.map(d => parseInt(d.total_dias));
                    const coloresPago = generarColores(dataPago.length);

                    new Chart(document.getElementById('graficoTortaPago').getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: labelsPago,
                            datasets: [{
                                data: dataPago,
                                backgroundColor: coloresPago
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'right'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = dataPago.reduce((a, b) => a + b, 0);
                                            const value = context.raw;
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return context.label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                },
                                datalabels: {
                                    formatter: (value, ctx) => {
                                        const total = ctx.chart.data.datasets[0].data
                                            .reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${percentage}%`; // ✅ Solo porcentaje en la torta
                                    },
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });


                    // -------------------------
                    // Exportar a PDF
                    // -------------------------
                    document.getElementById('btnPdf').addEventListener('click', async function(e) {
                        e.preventDefault();
                        await new Promise(resolve => setTimeout(resolve, 500));

                        const cargoImg = document.getElementById('graficoCargo').toDataURL('image/png', 1.0);
                        const linealImg = document.getElementById('graficoLinealMeses').toDataURL('image/png', 1.0);
                        const pagoImg = document.getElementById('graficoTortaPago').toDataURL('image/png', 1.0);

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
                        addField('grafico_img_pago', pagoImg);

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