<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
if (empty($_SESSION['Usuario_Nombre'])) {
    echo "Sesión expirada. Redirigiendo al inicio de sesión...";
    header('Refresh: 3; URL=cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$MiConexion = ConexionBD();

// Inicializar variables
$vacaciones_data = [];
$mensaje = $_GET['mensaje'] ?? '';
$fechaInicio = $fechaFin = $estado = '';

function convertirFecha($fecha)
{
    $partes = explode('/', $fecha);
    if (count($partes) === 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    return $fecha; // si ya está en formato correcto
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fechaInicio = convertirFecha($_POST['fechainicio'] ?? '');
    $fechaFin    = convertirFecha($_POST['fechafin'] ?? '');
    $estado      = $_POST['estado'] ?? '';
    $nombre      = $_POST['nombre'] ?? '';
    $apellido    = $_POST['apellido'] ?? '';

    // Validación de fechas
    if ($fechaInicio && $fechaFin && strtotime($fechaInicio) > strtotime($fechaFin)) {
        $mensaje = 'Error: La fecha de inicio no puede ser mayor que la fecha de fin.';
    } else {
        $params = [];
        $types = '';

        $query = "SELECT v.idvacaciones, e.nombre, e.apellido, c.descripcion AS cargo,
                         v.fecha_inicio, v.fecha_fin, v.cantidad_dias, v.vacaciones_restantes, v.estado
                  FROM vacaciones v
                  INNER JOIN empleado e ON v.idempleado = e.idempleado
                  INNER JOIN cargo c ON e.Idcargo = c.idcargo
                  WHERE 1";

        // Filtros dinámicos
        if ($fechaInicio && $fechaFin) {
            $query .= " AND v.fecha_inicio <= ? AND v.fecha_fin >= ?";
            $types .= 'ss';
            $params[] = $fechaFin;    // fin del rango
            $params[] = $fechaInicio; // inicio del rango
        }

        if ($estado) {
            $query .= " AND v.estado = ?";
            $types .= 's';
            $params[] = $estado;
        }

        if ($nombre) {
            $query .= " AND e.nombre LIKE ?";
            $types .= 's';
            $params[] = "%$nombre%";
        }

        if ($apellido) {
            $query .= " AND e.apellido LIKE ?";
            $types .= 's';
            $params[] = "%$apellido%";
        }

        $stmt = $MiConexion->prepare($query);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $MiConexion->error);
        }

        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $vacaciones_data = [];
        while ($row = $result->fetch_assoc()) {
            $vacaciones_data[] = $row;
        }

        if (empty($vacaciones_data)) {
            $vacaciones_data = []; // ya lo tenés inicializado
            // no asignar mensaje
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Vacaciones</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <meta charset="utf-8">

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

    <h5 class="card-title">Filtro de Busqueda</h5>
    <form method="POST" class="row g-3 my-4">
        <!-- Nombre -->
        <div class="col-md-4">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>

        <!-- Apellido -->
        <div class="col-md-4">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
        </div>

        <div class="col-md-4">
            <label for="fechainicio" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" name="fechainicio" value="<?= htmlspecialchars($fechaInicio) ?>">
        </div>
        <div class="col-md-4">
            <label for="fechafin" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" name="fechafin" value="<?= htmlspecialchars($fechaFin) ?>">
        </div>
        <div class="col-md-4">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado">
                <option value="">Todos</option>
                <option value="Pendiente" <?= (isset($_POST['estado']) && $_POST['estado'] === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                <option value="Aprobado" <?= (isset($_POST['estado']) && $_POST['estado'] === 'Aprobado') ? 'selected' : '' ?>>Aprobado</option>
                <option value="Rechazado" <?= (isset($_POST['estado']) && $_POST['estado'] === 'Rechazado') ? 'selected' : '' ?>>Rechazado</option>
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <?php if ($mensaje): ?>
        <div class="alert alert-warning"> <?= htmlspecialchars($mensaje) ?> </div>
    <?php endif; ?>

    <?php if (!empty($vacaciones_data)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Días Tomados</th>
                        <th>Días Restantes</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vacaciones_data as $vacacion): ?>
                        <tr>
                            <td><?= htmlspecialchars($vacacion['nombre'] . ' ' . $vacacion['apellido']) ?></td>
                            <td><?= htmlspecialchars($vacacion['cargo']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($vacacion['fecha_inicio']))) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($vacacion['fecha_fin']))) ?></td>
                            <td><?= htmlspecialchars($vacacion['cantidad_dias']) ?></td>
                            <td><?= htmlspecialchars($vacacion['vacaciones_restantes']) ?></td>

                            <!-- Estado actual -->
                            <td>
                                <span id="badge-<?= $vacacion['idvacaciones'] ?>" class="badge 
        <?= $vacacion['estado'] == 'Pendiente' ? 'bg-warning text-dark' : ($vacacion['estado'] == 'Aprobado' ? 'bg-success' : 'bg-danger') ?>">
                                    <?= htmlspecialchars($vacacion['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($vacacion['estado'] === 'Pendiente'): ?>
                                    <a href="#" class="cambiar-estado badge bg-success text-light"
                                        data-id="<?= $vacacion['idvacaciones'] ?>"
                                        data-estado="Aprobado"
                                        data-confirm="¿Seguro que deseas aprobar estas vacaciones?">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                    <a href="#" class="cambiar-estado badge bg-danger text-light"
                                        data-id="<?= $vacacion['idvacaciones'] ?>"
                                        data-estado="Rechazado"
                                        data-confirm="¿Seguro que deseas rechazar estas vacaciones?">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            No se encontraron registros de vacaciones.
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const botones = document.querySelectorAll('a.cambiar-estado');

            botones.forEach(boton => {
                boton.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (!confirm(boton.dataset.confirm)) return;

                    const idvacaciones = boton.dataset.id;
                    const nuevo_estado = boton.dataset.estado;

                    // DESHABILITAR EL BOTÓN QUE SE PRESIONÓ
                    boton.disabled = true;

                    fetch(`cambiar_estado_vacacion.php?idvacaciones=${idvacaciones}&nuevo_estado=${nuevo_estado}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Cambiar badge en la tabla
                                const badge = document.querySelector(`#badge-${idvacaciones}`);
                                badge.textContent = data.nuevo_estado;
                                badge.className = 'badge ' + (
                                    data.nuevo_estado === 'Pendiente' ? 'bg-warning text-dark' :
                                    data.nuevo_estado === 'Aprobado' ? 'bg-success' :
                                    'bg-danger'
                                );
                                // Deshabilitar ambos links de la fila
                                const fila = boton.closest('tr');
                                fila.querySelectorAll('a.cambiar-estado').forEach(b => {
                                    b.classList.add('disabled');
                                    b.style.pointerEvents = "none";
                                    b.removeAttribute('href');
                                });
                            } else {
                                alert(data.mensaje || 'Error desconocido');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Error al conectar con el servidor');
                            boton.disabled = false; // reactivar si hubo error
                        });
                });
            });
        });
    </script>
</body>

</html>