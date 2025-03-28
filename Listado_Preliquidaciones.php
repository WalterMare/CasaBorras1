<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

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
                                <a href="detalle_preliquidacion.php?id=<?php echo urlencode($preliquidacion['idpreliquidacion']); ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
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
    </div>
</body>
</html>
