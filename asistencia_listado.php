<?php
session_start();

// Verificación si la sesión está vacía y redirigir al login si es necesario
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';

// Manejo de errores en la conexión a la base de datos
try {
    $conexion = ConexionBD();
} catch (Exception $e) {
    die('Error en la conexión: ' . $e->getMessage());
}

date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaHoy = date('Y-m-d');
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
// date('N') devuelve 1 para lunes, 7 para domingo
$numeroDia = date('N');
$diaSemana = $dias[$numeroDia - 1];


$sql = "
SELECT e.idempleado, e.nombre, e.apellido,
       COALESCE(edh.hora_inicio, tdh.hora_inicio) AS hora_esperada_entrada,
       COALESCE(edh.hora_fin, tdh.hora_fin) AS hora_esperada_salida,
       a.idAsistencia, da.horasTrabajadas, es.nombreEstado,
       ea1.horaEvento AS horaEntrada, ea2.horaEvento AS horaSalida,
       da.observaciones
FROM empleado e
LEFT JOIN empleado_turno et ON e.idempleado = et.idempleado
LEFT JOIN turno_dia_horario tdh ON et.idturno = tdh.idturno AND tdh.dia_semana = '$diaSemana'
LEFT JOIN empleado_dia_horario edh ON e.idempleado = edh.idempleado AND edh.dia_semana = '$diaSemana'
LEFT JOIN asistencia a ON a.idEmpleado = e.idempleado AND a.fecha = '$fechaHoy'
LEFT JOIN estadoasistencia es ON a.idEstado = es.idEstado
LEFT JOIN detalle_asistencia da ON da.idAsistencia = a.idAsistencia
LEFT JOIN evento_asistencia ea1 ON ea1.idDetalleAsistencia = da.idDetalleAsistencia AND ea1.tipoEvento = 'Entrada'
LEFT JOIN evento_asistencia ea2 ON ea2.idDetalleAsistencia = da.idDetalleAsistencia AND ea2.tipoEvento = 'Salida'
ORDER BY e.apellido, e.nombre
";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Asistencia de Hoy</title>

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
            <h1 class="card-title text-center">Asistencia de Empleados</h1>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <h2>Asistencia de Hoy (<?php echo $fechaHoy; ?>)</h2>
                            <form action="asistencia_qr.php" method="POST" class="row g-6">
                                <div class="col-6">
                                    <label class="form-label">Empleado:</label>
                                    <select class="form-select" name="idEmpleado" required>
                                        <option value="">Seleccionar</option>
                                        <?php
                                        $empleados = $conexion->query("SELECT idempleado, nombre, apellido FROM empleado ORDER BY apellido");
                                        while ($emp = $empleados->fetch_assoc()) {
                                            echo "<option value='{$emp['idempleado']}'>{$emp['apellido']} {$emp['nombre']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-1">
                                    <button type="submit" name="accion" value="Entrada" class="btn btn-primary">Registrar Entrada</button>
                                    <button type="submit" name="accion" value="Salida" class="btn btn-secondary">Registrar Salida</button>
                                </div>

                            </form>

                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Empleado</th>
                                        <th>Horario Esperado</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                        <th>Horas Trabajadas</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $resultado->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['apellido'] . " " . $row['nombre'] ?></td>
                                            <td><?= $row['hora_esperada_entrada'] . " - " . $row['hora_esperada_salida'] ?></td>
                                            <td><?= $row['horaEntrada'] ?? "-" ?></td>
                                            <td><?= $row['horaSalida'] ?? "-" ?></td>
                                            <td><?= $row['nombreEstado'] ?? "Ausente" ?></td>
                                            <td><?= $row['horasTrabajadas'] ?? "-" ?></td>
                                            <td><?= $row['observaciones'] ?? "-" ?></td>
                                        </tr>
                                    <?php endwhile; ?>
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
