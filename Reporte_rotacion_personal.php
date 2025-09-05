<?php
session_start();
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// Filtros de fechas
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin    = $_GET['fechaFin'] ?? '';
$empleadosAltas = null;
$empleadosBajas = null;

if (!empty($fechaInicio) && !empty($fechaFin)) {

    // Opcional: obtener detalle de empleados ingresados y egresados
    $empleadosAltas = $conexion->query("
    SELECT e.nombre, e.apellido, c.descripcion AS cargo, e.fecha_inicio
    FROM empleado e
    LEFT JOIN cargo c ON e.idcargo = c.idcargo
    WHERE e.fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin'
");

    $empleadosBajas = $conexion->query("
    SELECT e.nombre, e.apellido, c.descripcion AS cargo, e.fecha_baja, e.motivo_baja
    FROM empleado e
    LEFT JOIN cargo c ON e.idcargo = c.idcargo
    WHERE e.fecha_baja BETWEEN '$fechaInicio' AND '$fechaFin'
");
}
// Consultas
require_once 'Consulta_RotacionPersonal.php';
$resultado = obtenerRotacionPersonal($conexion, $fechaInicio, $fechaFin);




?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Reporte</h1>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="container my-4">

                                <h2 class="mb-4">Reporte Completo de Rotación de Personal</h2>

                                <!-- Filtros -->
                                <form method="get" class="row g-3 mb-4">
                                    <div class="col-md-5">
                                        <label>Fecha Inicio:</label>
                                        <input type="date" class="form-control" name="fechaInicio" value="<?= $fechaInicio ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label>Fecha Fin:</label>
                                        <input type="date" class="form-control" name="fechaFin" value="<?= $fechaFin ?>" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                    </div>
                                    <hr>
                                </form>



                                <!-- Contenedor principal -->
                                <div class="container my-4">
                                    <div class="row">
                                        <!-- Columna de tablas (3/4) -->

                                        <div class="col-lg-9">
                                            <!-- Indicadores generales -->
                                            <div class="row mb-4">
                                                <div class="col-md-2">
                                                    <div class="card text-center bg-light">
                                                        <div class="card-body">
                                                            <h6>Altas</h6>
                                                            <p class="h5"><?= $resultado['Altas'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="card text-center bg-light">
                                                        <div class="card-body">
                                                            <h6>Bajas</h6>
                                                            <p class="h5"><?= $resultado['Bajas'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="card text-center bg-light">
                                                        <div class="card-body">
                                                            <h6>Plantilla Inicial</h6>
                                                            <p class="h5"><?= $resultado['PlantillaInicial'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="card text-center bg-light">
                                                        <div class="card-body">
                                                            <h6>Plantilla Final</h6>
                                                            <p class="h5"><?= $resultado['PlantillaFinal'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="card text-center bg-light">
                                                        <div class="card-body">
                                                            <h6>Plantilla Promedio</h6>
                                                            <p class="h5"><?= number_format($resultado['PlantillaPromedio'], 2) ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="card text-center bg-light">
                                                        <div class="card-body">
                                                            <h6>Rotación (%)</h6>
                                                            <p class="h5"><?= number_format($resultado['RotacionPorcentaje'], 2) ?>%</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h3>Detalle de Altas</h3>
                                            <table class="table table-bordered table-striped">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Apellido</th>
                                                        <th>Cargo</th>
                                                        <th>Fecha de Alta</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($empleadosAltas && $empleadosAltas->num_rows > 0): ?>
                                                        <?php while ($fila = $empleadosAltas->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?= $fila['nombre'] ?></td>
                                                                <td><?= $fila['apellido'] ?></td>
                                                                <td><?= $fila['cargo'] ?></td>
                                                                <td><?= $fila['fecha_inicio'] ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center">No se encontraron altas en el período</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>

                                            <h3 class="mt-5">Detalle de Bajas</h3>
                                            <table class="table table-bordered table-striped">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Apellido</th>
                                                        <th>Cargo</th>
                                                        <th>Fecha de Baja</th>
                                                        <th>Motivo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($empleadosBajas && $empleadosBajas->num_rows > 0): ?>
                                                        <?php while ($fila = $empleadosBajas->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?= $fila['nombre'] ?></td>
                                                                <td><?= $fila['apellido'] ?></td>
                                                                <td><?= $fila['cargo'] ?></td>
                                                                <td><?= $fila['fecha_baja'] ?></td>
                                                                <td><?= $fila['motivo_baja'] ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center">No se encontraron altas en el período</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Columna de gráficos (1/4) -->
                                        <div class="col-lg-3">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h6 class="text-center">Altas vs Bajas</h6>
                                                    <canvas id="graficoAltasBajas"></canvas>
                                                </div>
                                            </div>
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h6 class="text-center">Inicial vs Final</h6>
                                                    <canvas id="graficoPlantilla"></canvas>
                                                </div>
                                            </div>
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <h6 class="text-center">Rotación</h6>
                                                    <canvas id="graficoRotacion"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="exportar_excel.php?fechaInicio=<?= $fechaInicio ?>&fechaFin=<?= $fechaFin ?>" class="btn btn-success mb-3">
                                            Exportar a Excel
                                        </a>
                                    </div>
                                </div>



                                <script>
                                    const ctx1 = document.getElementById('graficoAltasBajas').getContext('2d');
                                    new Chart(ctx1, {
                                        type: 'bar',
                                        data: {
                                            labels: ['Altas', 'Bajas'],
                                            datasets: [{
                                                label: 'Cantidad de Empleados',
                                                data: [<?= $resultado['Altas'] ?>, <?= $resultado['Bajas'] ?>],
                                                backgroundColor: ['#6db66fff', '#f31808ff']
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    display: false
                                                }
                                            }
                                        }
                                    });

                                    const ctx2 = document.getElementById('graficoPlantilla').getContext('2d');
                                    new Chart(ctx2, {
                                        type: 'line',
                                        data: {
                                            labels: ['Plantilla Inicial', 'Plantilla Final'],
                                            datasets: [{
                                                label: 'Plantilla',
                                                data: [<?= $resultado['PlantillaInicial'] ?>, <?= $resultado['PlantillaFinal'] ?>],
                                                borderColor: '#2196F3',
                                                backgroundColor: '#BBDEFB',
                                                fill: true,
                                                tension: 0.4
                                            }]
                                        },
                                        options: {
                                            responsive: true
                                        }
                                    });
                                </script>

                                <script>
                                    const ctx3 = document.getElementById('graficoRotacion').getContext('2d');

                                    const rotacion = <?= number_format($resultado['RotacionPorcentaje'], 2) ?>;
                                    const noRotacion = 100 - rotacion;

                                    new Chart(ctx3, {
                                        type: 'pie',
                                        data: {
                                            labels: ['Rotación', 'Plantilla Estable'],
                                            datasets: [{
                                                data: [rotacion, noRotacion],
                                                backgroundColor: ['#d81608ff', '#86b687ff']
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
                                                            return context.label + ': ' + context.parsed + '%';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                </script>



                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>