<?php
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires');
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();

// Procesar escaneo QR
if (isset($_GET['idEmpleado'])) {
    $idEmpleado = (int) $_GET['idEmpleado'];
    $fechaHoy = date('Y-m-d');
    $horaActual = date('H:i:s');
    $diaSemana = date('l', strtotime($fechaHoy));

    // Obtener horario asignado para hoy
    $queryHorario = "SELECT hora_inicio FROM empleado_dia_horario WHERE idempleado = ? AND dia_semana = ?";
    $stmtHorario = mysqli_prepare($conexion, $queryHorario);
    mysqli_stmt_bind_param($stmtHorario, 'is', $idEmpleado, $diaSemana);
    mysqli_stmt_execute($stmtHorario);
    mysqli_stmt_bind_result($stmtHorario, $horaInicio);
    mysqli_stmt_fetch($stmtHorario);
    mysqli_stmt_close($stmtHorario);

    // Verificar si ya registró entrada hoy
    $queryAsistencia = "SELECT idAsistencia, horaEntrada FROM asistencias WHERE idEmpleado = ? AND fecha = ?";
    $stmtAsistencia = mysqli_prepare($conexion, $queryAsistencia);
    mysqli_stmt_bind_param($stmtAsistencia, 'is', $idEmpleado, $fechaHoy);
    mysqli_stmt_execute($stmtAsistencia);
    mysqli_stmt_bind_result($stmtAsistencia, $idAsistencia, $horaEntrada);
    mysqli_stmt_fetch($stmtAsistencia);
    mysqli_stmt_close($stmtAsistencia);

    if ($idAsistencia) {
        // Registrar salida
        $updateSalida = "UPDATE asistencias SET horaSalida = ? WHERE idAsistencia = ?";
        $stmtSalida = mysqli_prepare($conexion, $updateSalida);
        mysqli_stmt_bind_param($stmtSalida, 'si', $horaActual, $idAsistencia);
        mysqli_stmt_execute($stmtSalida);
        mysqli_stmt_close($stmtSalida);
        $mensaje = "Salida registrada correctamente.";
    } else {
        // Determinar estado
        $estado = ($horaActual <= $horaInicio) ? 'Presente' : 'Tarde';
        $observaciones = $estado === 'Tarde' ? 'Llegada fuera del horario asignado' : null;

        // Registrar entrada
        $insertAsistencia = "INSERT INTO asistencias (idEmpleado, fecha, horaEntrada, estado, observaciones) VALUES (?, ?, ?, ?, ?)";
        $stmtEntrada = mysqli_prepare($conexion, $insertAsistencia);
        mysqli_stmt_bind_param($stmtEntrada, 'issss', $idEmpleado, $fechaHoy, $horaActual, $estado, $observaciones);
        mysqli_stmt_execute($stmtEntrada);
        mysqli_stmt_close($stmtEntrada);
        $mensaje = "Entrada registrada correctamente.";
    }
}

// Obtener listado de empleados y asistencia de hoy
$consulta = "SELECT e.idempleado, e.nombre, e.apellido, a.fecha, a.horaEntrada, a.horaSalida, a.estado, a.observaciones FROM empleado e LEFT JOIN asistencias a ON e.idempleado = a.idEmpleado AND a.fecha = CURDATE() ORDER BY e.apellido, e.nombre";
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
            const idEmpleado = prompt("Ingrese el ID del empleado para simular escaneo QR:");
            if (idEmpleado) window.location.href = `?idEmpleado=${idEmpleado}`;
        }
    </script>
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
                            <h1 class="card-title text-center">Control de Asistencia</h1>

                            <?php if (isset($mensaje)) { ?>
                                <div class="alert alert-success text-center" role="alert">
                                    <?= $mensaje ?>
                                </div>
                            <?php } ?>

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
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include_once 'partes/footer.php'; ?>
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