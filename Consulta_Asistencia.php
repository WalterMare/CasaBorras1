<?php
require_once 'conexiondb.php';
$conexion = ConexionBD();

// Obtener empleados para el select
$empleados = $conexion->query("SELECT idempleado, nombre, apellido FROM empleado ORDER BY apellido, nombre");
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Consulta de Asistencia</title>
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

<body>
    <div class="container mt-4">
        <h1 class="mb-4">Consulta de Asistencia por Empleado</h1>
        <form method="GET" action="" class="row g-3 ">
            <div class="col-md-3 d-flex flex-column">
                <label for="idEmpleado" class="form-label">Empleado</label>
                <select name="idEmpleado" id="idEmpleado" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php while ($row = $empleados->fetch_assoc()): ?>
                        <option value="<?= $row['idempleado'] ?>" <?= (isset($_GET['idEmpleado']) && $_GET['idEmpleado'] == $row['idempleado']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['apellido'] . ', ' . $row['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex flex-column">
                <label for="fechaInicio" class="form-label">Desde</label>
                <input type="date" id="fechaInicio" name="fechaInicio" class="form-control" value="<?= $_GET['fechaInicio'] ?? '' ?>" />
            </div>
            <div class="col-md-3 d-flex flex-column">
                <label for="fechaFin" class="form-label">Hasta</label>
                <input type="date" id="fechaFin" name="fechaFin" class="form-control" value="<?= $_GET['fechaFin'] ?? '' ?>" />
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </form>

        <?php
        if (!empty($_GET['idEmpleado'])):
            $idEmpleado = intval($_GET['idEmpleado']);
            $fechaInicio = $_GET['fechaInicio'] ?? null;
            $fechaFin = $_GET['fechaFin'] ?? null;

            $condFecha = '';
            $params = [$idEmpleado];
            $tipos = "i";

            if ($fechaInicio && $fechaFin) {
                $condFecha = " AND a.fecha BETWEEN ? AND ? ";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
                $tipos .= "ss";
            } elseif ($fechaInicio) {
                $condFecha = " AND a.fecha >= ? ";
                $params[] = $fechaInicio;
                $tipos .= "s";
            } elseif ($fechaFin) {
                $condFecha = " AND a.fecha <= ? ";
                $params[] = $fechaFin;
                $tipos .= "s";
            }

            $sql = "SELECT a.fecha, ea.nombreEstado, da.observaciones, da.horasTrabajadas,
           (SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento='Entrada' ORDER BY idEvento ASC LIMIT 1) AS horaEntrada,
           (SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento='Salida' ORDER BY idEvento DESC LIMIT 1) AS horaSalida
    FROM asistencia a
    LEFT JOIN estadoasistencia ea ON a.idEstado = ea.idEstado
    LEFT JOIN detalle_asistencia da ON a.idAsistencia = da.idAsistencia
    WHERE a.idEmpleado = ? $condFecha
    ORDER BY a.fecha DESC";

            $stmt = $conexion->prepare($sql);

            // bind_param dinámico para máximo 3 parámetros
            if (count($params) === 1) {
                $stmt->bind_param($tipos, $params[0]);
            } elseif (count($params) === 2) {
                $stmt->bind_param($tipos, $params[0], $params[1]);
            } elseif (count($params) === 3) {
                $stmt->bind_param($tipos, $params[0], $params[1], $params[2]);
            }

            $stmt->execute();
            $resultados = $stmt->get_result();
        ?>

            <table class="table table-bordered table-striped align-middle text-center mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        <th>Horas Trabajadas</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultados->num_rows > 0): ?>
                        <?php while ($fila = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['fecha']) ?></td>
                                <td><?= htmlspecialchars($fila['nombreEstado'] ?? 'Sin Estado') ?></td>
                                <td><?= htmlspecialchars($fila['horaEntrada'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($fila['horaSalida'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($fila['horasTrabajadas'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($fila['observaciones'] ?? '') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No se encontraron registros.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>

<?php
            $stmt->close();
        endif;
?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>