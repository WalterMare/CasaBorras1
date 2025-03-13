<?php
session_start();

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
$mensaje = '';
$fechaInicio = $fechaFin = $estado = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fechaInicio = $_POST['fechainicio'] ?? '';
    $fechaFin = $_POST['fechafin'] ?? '';
    $estado = $_POST['estado'] ?? '';

    if ($fechaInicio && $fechaFin && strtotime($fechaInicio) > strtotime($fechaFin)) {
        $mensaje = 'Error: La fecha de inicio no puede ser mayor que la fecha de fin.';
    } else {
        $query = "SELECT v.idvacaciones, e.nombre, e.apellido, c.descripcion AS cargo, v.fecha_inicio, v.fecha_fin, 
                         v.cantidad_dias, v.vacaciones_restantes, v.estado
                  FROM vacaciones v
                  INNER JOIN empleado e ON v.idempleado = e.idempleado
                  INNER JOIN cargo c ON e.Idcargo = c.idcargo
                  WHERE 1";

        if ($fechaInicio && $fechaFin) {
            $query .= " AND v.fecha_inicio BETWEEN ? AND ?";
        }
        if ($estado) {
            $query .= " AND v.estado = ?";
        }

        $stmt = $MiConexion->prepare($query);

        if ($fechaInicio && $fechaFin && $estado) {
            $stmt->bind_param('sss', $fechaInicio, $fechaFin, $estado);
        } elseif ($fechaInicio && $fechaFin) {
            $stmt->bind_param('ss', $fechaInicio, $fechaFin);
        } elseif ($estado) {
            $stmt->bind_param('s', $estado);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $vacaciones_data[] = $row;
        }

        if (!$vacaciones_data) {
            $mensaje = "No se encontraron registros.";
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
    <?php include 'partes/header.php'; ?>
    <?php include 'partes/menu.php'; ?>

    <main id='main' class="main">

    <div class="pagetitle">
      <h1>Vacaciones</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de movimientos</li>
          <li class="breadcrumb-item active">Consultar Vacaciones</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Filtro de Busqueda</h5>
                            <form method="POST" class="row g-3 my-4">
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
                                    <select class="form-control" name="estado">
                                        <option value="">Todos</option>
                                        <option value="Pendiente" <?= $estado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="Aprobado" <?= $estado === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                        <option value="Rechazado" <?= $estado === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Buscar</button>
                                </div>
                            </form>

                            <?php if ($mensaje): ?>
                                <div class="alert alert-warning"> <?= htmlspecialchars($mensaje) ?> </div>
                            <?php endif; ?>

                            <?php if (!empty($vacaciones_data)): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Cargo</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Días Tomados</th>
                                            <th>Días Restantes</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vacaciones_data as $vacacion): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($vacacion['nombre'] . ' ' . $vacacion['apellido']) ?></td>
                                                <td><?= htmlspecialchars($vacacion['cargo']) ?></td>
                                                <td><?= htmlspecialchars($vacacion['fecha_inicio']) ?></td>
                                                <td><?= htmlspecialchars($vacacion['fecha_fin']) ?></td>
                                                <td><?= htmlspecialchars($vacacion['cantidad_dias']) ?></td>
                                                <td><?= htmlspecialchars($vacacion['vacaciones_restantes']) ?></td>
                                                <td><?= htmlspecialchars($vacacion['estado']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>                    
    </main>

    <?php include 'partes/footer.php'; ?>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>