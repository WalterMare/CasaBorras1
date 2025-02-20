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

// Obtener listado de empleados y su última asistencia
$consulta = "SELECT e.idempleado, e.nombre, e.apellido, a.fecha, a.horaEntrada, a.horaSalida, a.estado, a.observaciones 
             FROM empleado e
             LEFT JOIN asistencias a ON e.idempleado = a.idEmpleado 
             AND a.fecha = CURDATE()
             ORDER BY e.apellido, e.nombre";
$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html lang="es">

<head>
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

  
    <script>
        function escanearQR() {
            window.location.href = "escanear_qr.php";
        }

        function generarQR(idEmpleado) {
            window.location.href = "generar_qr.php?idEmpleado=" + idEmpleado;
        }
    </script>
</head>

<body class="bg-light">
    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php' ?>
    <!-- End Header -->
    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>
    <!-- End Sidebar-->
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Asistencia de Empleados</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de movimientos</li>
                    <li class="breadcrumb-item active">Registro de asistencias</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="card-title text-center">Control de Asistencia</h1>

                            <div class="d-flex justify-content-center my-3">
                                <button class="btn btn-primary" onclick="escanearQR()">📷 Escanear QR</button>
                            </div>

                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Fecha</th>
                                        <th>Hora Entrada</th>
                                        <th>Hora Salida</th>
                                        <th>Estado</th>
                                        <th>Observaciones</th>
                                        <th>QR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                        <tr>
                                            <td><?= $fila['idempleado'] ?></td>
                                            <td><?= $fila['nombre'] ?></td>
                                            <td><?= $fila['apellido'] ?></td>
                                            <td><?= $fila['fecha'] ?: '---' ?></td>
                                            <td><?= $fila['horaEntrada'] ?: '---' ?></td>
                                            <td><?= $fila['horaSalida'] ?: '---' ?></td>
                                            <td>
                                                <span class="badge bg-<?= $fila['estado'] == 'Presente' ? 'success' : ($fila['estado'] == 'Tarde' ? 'warning' : 'danger') ?>">
                                                    <?= $fila['estado'] ?: 'Ausente' ?>
                                                </span>
                                            </td>
                                            <td><?= $fila['observaciones'] ?: '---' ?></td>
                                            <td>
                                                <button class="btn btn-primary" onclick="generarQR(<?= $fila['idempleado'] ?>)">📄 Generar QR</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main><!-- End #main -->


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

    <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>


</body>

</html>