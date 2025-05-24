<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// Parámetros de paginación
$porPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $porPagina;

// Obtener total de preliquidaciones
$totalConsulta = "SELECT COUNT(*) AS total FROM preliquidacion";
$rsTotal = mysqli_query($conexion, $totalConsulta);
$totalFilas = mysqli_fetch_assoc($rsTotal)['total'];
$totalPaginas = ceil($totalFilas / $porPagina);


// Función para obtener preliquidaciones
function Listar_Preliquidaciones($vConexion)
{
    $Listado = array();

    $consulta = "SELECT 
                    p.idpreliquidacion,
                    p.fecha, 
                    p.periodo,
                    ep.descripcionPreliquidacion AS estado
                FROM 
                    preliquidacion p
                JOIN 
                    estadopreliquidacion ep ON p.idEstadoPre = ep.idestadoPreliquidacion
                ORDER BY 
                    p.fecha DESC";

    $rs = mysqli_query($vConexion, $consulta);
    if (!$rs) {
        die("Error en la consulta: " . mysqli_error($vConexion));
    }

    while ($data = mysqli_fetch_array($rs, MYSQLI_ASSOC)) {
        $Listado[] = $data;
    }

    return $Listado;
}

$preliquidaciones = Listar_Preliquidaciones($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Listado de Preliquidaciones</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h5 class="card-title">Preliquidaciones</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Período</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($preliquidaciones)) { ?>
                    <?php foreach ($preliquidaciones as $preliquidacion) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($preliquidacion['fecha']))); ?></td>
                            <td><?php echo htmlspecialchars($preliquidacion['periodo']); ?></td>
                            <td><?php echo htmlspecialchars($preliquidacion['estado']); ?></td>
                            <td>
                                <a href="detalle_preliquidacion.php?id=<?php echo urlencode($preliquidacion['idpreliquidacion']); ?>" class="btn btn-warning btn-sm">Ver Detalles</a>
                                <?php if ($preliquidacion['estado'] ==='Confirmada'): ?>
                                    <a href="exportar_preliquidacion.php?id=<?php echo $preliquidacion['idpreliquidacion']; ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                                    </a>
                                <?php else: ?>
                                    <!-- Opcional: botón deshabilitado o mensaje -->
                                    <button class="btn btn-success btn-sm" disabled title="Solo disponible cuando está Confirmada">
                                        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                                    </button>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay preliquidaciones registradas.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Paginación -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
                    <li class="page-item <?php echo $i == $paginaActual ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"> <?php echo $i; ?> </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</body>

</html>