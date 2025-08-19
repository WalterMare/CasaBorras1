<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Usuario_Nombre'])) {
    header("Location: cerrarsesion.php");
    exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();

$sql = "SELECT v.*, e.nombre, e.apellido
        FROM vacaciones v
        INNER JOIN empleado e ON e.idempleado = v.idempleado
        ORDER BY v.anio DESC, v.fecha_inicio DESC";
$res = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Vacaciones</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
<h3>Vacaciones Registradas</h3>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Empleado</th>
            <th>Año</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Días</th>
            <th>Restantes</th>
            <th>Estado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['apellido'] . ', ' . $row['nombre'] ?></td>
            <td><?= $row['anio'] ?></td>
            <td><?= $row['fecha_inicio'] ?></td>
            <td><?= $row['fecha_fin'] ?></td>
            <td><?= $row['cantidad_dias'] ?></td>
            <td><?= $row['vacaciones_restantes'] ?></td>
            <td><?= $row['estado'] ?></td>
            <td><?= $row['observaciones'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
