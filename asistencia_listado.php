<?php
session_start();
require_once 'conexiondb.php';
$conexion = ConexionBD();
$mensaje = $_GET['mensaje'] ?? '';

$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

$result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM empleado");
$totalRegistros = mysqli_fetch_assoc($result)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

require_once 'Select_Asistencia.php';

$CantidadAsistencia = 0;
$listadoAsistencia = Listar_asistenciasHoy($conexion, $registrosPorPagina, $offset);
$CantidadAsistencia = count($listadoAsistencia);
require_once 'asistencia_qr.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Listado de Asistencias</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia</title>
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

<body class="bg-light">
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Asistencia de Empleados</h1>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <h2>Listado de Asistencias de Hoy</h2>
                            <?php if (isset($_GET['mensaje'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-center my-3">
                                <button class="btn btn-primary" onclick="escanearQR()">📷 Escanear QR</button>
                            </div>

                            <table class="table table-striped">
                                <thead class="table-whrite">
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                        <th>Observación Entrada</th>
                                        <th>Observación Salida</th>
                                    </tr>
                                </thead>
                                <?php if ($CantidadAsistencia != 0 && $CantidadAsistencia != null) { ?>
                                    <tbody>
                                        <?php for ($i = 0; $i < $CantidadAsistencia; $i++) { ?>
                                            <tr>
                                                <td><?php echo $listadoAsistencia[$i]['IDEMPLEADO'] ?? '-' ?></td>
                                                <td><?php echo $listadoAsistencia[$i]['NOMBRE'] . ' ' . $listadoAsistencia[$i]['APELLIDO'] ?></td>
                                                <td><?php echo $listadoAsistencia[$i]['ENTRADA'] ?? '-' ?></td>
                                                <td><?php echo $listadoAsistencia[$i]['SALIDA'] ?? '-' ?></td>
                                                <td><span class="badge bg-<?php echo $listadoAsistencia[$i]['ESTADO'] == 'Presente' ? 'success' : 'danger' ?>">
                                                        <?php echo $listadoAsistencia[$i]['ESTADO'] ?: 'Ausente' ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $listadoAsistencia[$i]['OBS_ENTRADA'] ?? '-' ?></td>
                                                <td><?php echo $listadoAsistencia[$i]['OBS_SALIDA'] ?? '-' ?></td>
                                            </tr>
                                        <?php }; ?>
                                    </tbody>
                                <?php }; ?>

                            </table>
                            <nav aria-label="Paginación">
                                <ul class="pagination justify-content-center">
                                    <?php if ($paginaActual > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>" aria-label="Anterior">
                                                <span aria-hidden="true">&laquo; Anterior</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($paginaActual < $totalPaginas): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>" aria-label="Siguiente">
                                                <span aria-hidden="true">Siguiente &raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>

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
    <script src="assets/js/main.js"></script>

</body>

</html>