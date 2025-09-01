<?php
session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// Variables para el filtro de fechas
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';
$empleado    = $_GET['empleado'] ?? '';
$dni         = $_GET['dni'] ?? '';
$ordenColumna  = $_GET['ordenColumna'] ?? 'empleado';  // Valor por defecto
$ordenDireccion = $_GET['ordenDireccion'] ?? 'ASC';     // Ascendente por defecto


// Convertir las fechas a formato de timestamp
$timestampInicio = strtotime($fechaInicio);
$timestampFin = strtotime($fechaFin);

require_once 'Consulta_asistencia_Global.php';
$resultado = Obtener_Resumen_Asistencia_Completo(
    $conexion,
    $fechaInicio,
    $fechaFin,
    $empleado,
    $dni,
    $ordenColumna,
    $ordenDireccion
);


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
</head>

<body>
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Reporte de Asistencia Global</h1>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="mt-4">Buscar</h2>
                            <form method="get" class="my-3">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>Fecha Inicio:</label>
                                        <input type="date" class="form-control" name="fechaInicio" value="<?= $fechaInicio ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label>Fecha Fin:</label>
                                        <input type="date" class="form-control" name="fechaFin" value="<?= $fechaFin ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="empleado" class="form-label">Empleado</label>
                                        <input type="text" name="empleado" id="empleado" class="form-control" placeholder="Apellido o Nombre" value="<?= htmlspecialchars($_GET['empleado'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="dni" class="form-label">DNI</label>
                                        <input type="text" name="dni" id="dni" class="form-control" placeholder="DNI" value="<?= htmlspecialchars($_GET['dni'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="ordenColumna" class="form-label">Ordenar por</label>
                                        <select name="ordenColumna" id="ordenColumna" class="form-select">
                                            <option value="empleado" <?= ($ordenColumna == 'empleado') ? 'selected' : '' ?>>Empleado</option>
                                            <option value="dni" <?= ($ordenColumna == 'dni') ? 'selected' : '' ?>>DNI</option>
                                            <option value="diasTrabajados" <?= ($ordenColumna == 'diasTrabajados') ? 'selected' : '' ?>>Días Trabajados</option>
                                            <option value="diasLaboralesProgramados" <?= ($ordenColumna == 'diasLaboralesProgramados') ? 'selected' : '' ?>>Días Laborales Programados</option>
                                            <option value="inasistencias" <?= ($ordenColumna == 'inasistencias') ? 'selected' : '' ?>>Inasistencias</option>
                                            <option value="porcentajeAsistencia" <?= ($ordenColumna == 'porcentajeAsistencia') ? 'selected' : '' ?>>% Asistencia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="ordenDireccion" class="form-label">Dirección</label>
                                        <select name="ordenDireccion" id="ordenDireccion" class="form-select">
                                            <option value="ASC" <?= ($ordenDireccion == 'ASC') ? 'selected' : '' ?>>Ascendente</option>
                                            <option value="DESC" <?= ($ordenDireccion == 'DESC') ? 'selected' : '' ?>>Descendente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 align-self-end">
                                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                    </div>

                                </div>
                            </form>


                            <?php if (!empty($resultado)) : ?>
                                <h2 class="mt-4">Resultados</h2>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mt-3">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID Empleado</th>
                                                <th>Empleado</th>
                                                <th>DNI</th>
                                                <th>Días Laborales Programados</th>
                                                <th>Días Trabajados</th>
                                                <th>Inasistencias</th>
                                                <th>% Asistencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($resultado as $row) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['idEmpleado']) ?></td>
                                                    <td><?= htmlspecialchars($row['empleado']) ?></td>
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars($row['diasLaboralesProgramados']) ?></td>
                                                    <td><?= htmlspecialchars($row['diasTrabajados']) ?></td>
                                                    <td><?= htmlspecialchars($row['inasistencias']) ?></td>
                                                    <td><?= number_format($row['porcentajeAsistencia'], 2) ?> %</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <p class="mt-4 text-danger">⚠️ No se encontraron registros en el rango de fechas seleccionado.</p>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>