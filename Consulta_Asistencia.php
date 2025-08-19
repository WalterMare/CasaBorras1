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
        <h3 class="mb-4">Consulta de Asistencia por Empleado</h3>
        <form method="GET" action="" class="row g-3 ">
            <div class="col-md-3 d-flex flex-column">
                <label for="idEmpleado" class="form-label">Empleado</label>
                <select name="idEmpleado" id="idEmpleado" class="form-select">
                    <option value="">- Todos los empleados --</option>
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
        $idEmpleado = isset($_GET['idEmpleado']) && $_GET['idEmpleado'] !== '' ? intval($_GET['idEmpleado']) : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        $condiciones = [];
        $params = [];
        $tipos = '';

        // Si hay empleado seleccionado
        if ($idEmpleado !== null) {
            $condiciones[] = "a.idEmpleado = ?";
            $params[] = $idEmpleado;
            $tipos .= 'i';
        }

        // Condición de fechas
        if ($fechaInicio && $fechaFin) {
            $condiciones[] = "a.fecha BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
            $tipos .= 'ss';
        } elseif ($fechaInicio) {
            $condiciones[] = "a.fecha >= ?";
            $params[] = $fechaInicio;
            $tipos .= 's';
        } elseif ($fechaFin) {
            $condiciones[] = "a.fecha <= ?";
            $params[] = $fechaFin;
            $tipos .= 's';
        }

        // Construir WHERE
        $whereSql = '';
        if (count($condiciones) > 0) {
            $whereSql = "WHERE " . implode(' AND ', $condiciones);
        }

        $sql = "SELECT 
    e.apellido, 
    e.nombre,
    a.fecha, 
    ea.nombreEstado, 
    da.observaciones, 
    da.horasTrabajadas,
    (SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento='Entrada' ORDER BY idEvento ASC LIMIT 1) AS horaEntrada,
    (SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento='Salida' ORDER BY idEvento DESC LIMIT 1) AS horaSalida
FROM asistencia a
LEFT JOIN estadoasistencia ea ON a.idEstado = ea.idEstado
LEFT JOIN detalle_asistencia da ON a.idAsistencia = da.idAsistencia
LEFT JOIN empleado e ON a.idEmpleado = e.idempleado
$whereSql
ORDER BY e.apellido, e.nombre, a.fecha DESC
";

        $stmt = $conexion->prepare($sql);

        if (count($params) > 0) {
            // bind_param necesita referencias, por eso armamos un array
            $bind_names[] = $tipos;
            for ($i = 0; $i < count($params); $i++) {
                $bind_names[] = &$params[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }

        $stmt->execute();
        $resultados = $stmt->get_result();

        ?>

        <table class="table table-bordered table-striped align-middle text-center mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Horas Trabajadas</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $empleadoActual = null;
                while ($fila = $resultados->fetch_assoc()):
                    $nombreEmpleado = htmlspecialchars($fila['apellido'] . ', ' . $fila['nombre']);
                    if ($empleadoActual !== $nombreEmpleado) {
                        // Si querés separar con fila de título por empleado
                        if ($empleadoActual !== null) {
                            echo "<tr><td colspan='7'>&nbsp;</td></tr>"; // espacio entre empleados
                        }
                        $empleadoActual = $nombreEmpleado;
                    }
                ?>
                    <tr>
                        <td style="font-size: 11px"><strong><?= $nombreEmpleado ?></strong></td>
                        <td style="font-size: 11px"><?= htmlspecialchars($fila['fecha']) ?></td>
                        <td style="font-size: 11px"><?= htmlspecialchars($fila['nombreEstado'] ?? 'Sin Estado') ?></td>
                        <td style="font-size: 11px"><?= htmlspecialchars($fila['horaEntrada'] ?? '-') ?></td>
                        <td style="font-size: 11px"><?= htmlspecialchars($fila['horaSalida'] ?? '-') ?></td>
                        <td style="font-size: 11px"><?= htmlspecialchars($fila['horasTrabajadas'] ?? '-') ?></td>
                        <td style="font-size: 11px"><?= htmlspecialchars($fila['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php
    $stmt->close();
    ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>