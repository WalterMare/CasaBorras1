<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

require_once 'controlador_asistencia.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaHoy = date('Y-m-d');
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
// date('N') devuelve 1 para lunes, 7 para domingo
$numeroDia = date('N');
$diaSemana = $dias[$numeroDia - 1];

$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Contar el total de registros
$sqlTotal = "
SELECT COUNT(*) AS total
FROM empleado e
LEFT JOIN empleado_turno et ON e.idempleado = et.idempleado
LEFT JOIN turno_dia_horario tdh ON et.idturno = tdh.idturno AND tdh.dia_semana = '$diaSemana'
LEFT JOIN empleado_dia_horario edh ON e.idempleado = edh.idempleado AND edh.dia_semana = '$diaSemana'
LEFT JOIN asistencia a ON a.idEmpleado = e.idempleado AND a.fecha = '$fechaHoy'
LEFT JOIN estadoasistencia es ON a.idEstado = es.idEstado
LEFT JOIN detalle_asistencia da ON da.idAsistencia = a.idAsistencia
LEFT JOIN evento_asistencia ea1 ON ea1.idDetalleAsistencia = da.idDetalleAsistencia AND ea1.tipoEvento = 'Entrada'
LEFT JOIN evento_asistencia ea2 ON ea2.idDetalleAsistencia = da.idDetalleAsistencia AND ea2.tipoEvento = 'Salida'
";
$totalResultado = $conexion->query($sqlTotal);
$totalRegistros = $totalResultado->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

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
LIMIT $registrosPorPagina OFFSET $offset
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

    <h2>Asistencia de Hoy (<?php echo $fechaHoy; ?>)</h2>

    <?php
    $Mensaje = '';
    $Estilo = '';

    // Si viene un POST para registrar entrada/salida
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $idEmpleado = intval($_POST['idEmpleado']);
        $accion = $_POST['accion'];

        if ($accion === 'Entrada') {
            $Mensaje = registrarEntrada($idEmpleado, $conexion);
        } elseif ($accion === 'Salida') {
            $Mensaje = registrarSalida($idEmpleado, $conexion);
        }

        // Definir estilo según el tipo de mensaje
        if (strpos($Mensaje, 'correctamente') !== false) {
            $Estilo = 'success';
        } else {
            $Estilo = 'warning';
        }
    } ?>

    <?php if (!empty($Mensaje)) { ?>
        <div id='cartel' class="alert alert-<?php echo $Estilo; ?> alert-dismissible fade show" role="alert">
            <i class="bi <?php echo $Estilo === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'; ?> me-1"></i>
            <?php echo $Mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <form method="POST" class="row g-6">
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
        <div class="col-6 d-flex gap-2 align-items-end">
            <button type="submit" name="accion" value="Entrada" class="btn btn-primary">Registrar Entrada</button>
            <button type="submit" name="accion" value="Salida" class="btn btn-secondary">Registrar Salida</button>
        </div>

    </form>

    <table class="table table-striped mt-4">
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
                    <td>
                        <?php
                        $estado = $row['nombreEstado'] ?? 'Ausente';
                        $badgeClass = ($estado === 'Presente') ? 'bg-success' : 'bg-danger';
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>
                    </td>
                    <td><?= $row['horasTrabajadas'] ?? "-" ?></td>
                    <td><?= $row['observaciones'] ?? "-" ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <nav aria-label="Paginación de asistencia" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($paginaActual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>">Anterior</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>">Siguiente</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</body>

</html>