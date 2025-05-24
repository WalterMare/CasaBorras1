<?php
session_start();
require_once 'conexiondb.php';
$conexion = ConexionBD();

if (!isset($_GET['id'])) {
    die("Error: No se proporcionó una preliquidación válida.");
}

$idPreliquidacion = intval($_GET['id']);

// Función para obtener detalles de la preliquidación (incluye ID del estado)
function Obtener_Detalles_Preliquidacion($vConexion, $idPreliquidacion)
{
    $consulta = "SELECT 
                    p.fecha, 
                    p.periodo,  
                    ep.descripcionPreliquidacion AS estado,
                    ep.idestadoPreliquidacion AS idEstado
                 FROM 
                    preliquidacion p
                 JOIN 
                    estadopreliquidacion ep ON p.idEstadoPre = ep.idestadoPreliquidacion
                 WHERE 
                    p.idpreliquidacion = ?";
    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Función para listar empleados (sin cambios)
function Listar_Empleados_Preliquidacion($vConexion, $idPreliquidacion)
{
    $Listado = array();
    $consulta = "SELECT e.idempleado, e.nombre, e.apellido FROM detallepreliquidacion dp JOIN empleado e ON dp.idEmpleado = e.idempleado WHERE dp.idPreliquidacion = ?";
    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($data = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $Listado[] = $data;
    }
    return $Listado;
}

$preliquidacion = Obtener_Detalles_Preliquidacion($conexion, $idPreliquidacion);
$empleados = Listar_Empleados_Preliquidacion($conexion, $idPreliquidacion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Detalles de Preliquidación</title>
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

    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include 'partes/header.php'; ?>
    <?php include 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Detalles de Preliquidación</h1>
            <nav><!-- Breadcrumb sin cambios --></nav>
        </div>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show mt-3 mx-3" role="alert">'
                . $_SESSION['error'] .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
            unset($_SESSION['error']);
        }
        ?>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Encabezado y datos básicos (existente) -->
                            <h2>Detalles de Preliquidación</h2>
                            <p><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($preliquidacion['fecha'])); ?></p>
                            <p><strong>Período:</strong> <?php echo $preliquidacion['periodo']; ?></p>

                            <!-- --- AGREGAR AQUÍ EL CÓDIGO DE ESTADO Y BOTONES --- -->

                            <!-- Mostrar estado actual -->
                            <?php
                            $estado = $preliquidacion['idEstado'];
                            $clase = 'bg-success';
                            $ancho = '100%';

                            if ($estado == 1) {
                                $clase = 'bg-warning progress-bar-striped';
                                $ancho = '33%';
                            } elseif ($estado == 2) {
                                $clase = 'bg-info';
                                $ancho = '66%';
                            }
                            ?>

                            <div class="progress mt-3">
                                <div class="progress-bar <?php echo $clase; ?>" style="width: <?php echo $ancho; ?>">
                                    <?php echo $preliquidacion['estado']; ?>
                                </div>
                            </div>

                            <!-- Botones condicionales -->
                            <div class="mt-3 mb-4">
                                <?php if ($preliquidacion['idEstado'] == 1): ?>
                                    <a href="generar_calculos.php?id=<?php echo $idPreliquidacion; ?>"
                                        class="btn btn-primary"
                                        onclick="return confirm('¿Generar cálculos?')">
                                        <i class="bi bi-calculator"></i> Generar Cálculos
                                    </a>

                                <?php elseif ($preliquidacion['idEstado'] == 2): ?>
                                    <a href="confirmar_preliquidacion.php?id=<?php echo $idPreliquidacion; ?>"
                                        class="btn btn-success"
                                        onclick="return confirm('¿Está seguro de confirmar esta preliquidación? Esta acción no se puede deshacer.')">
                                        <i class="bi bi-check-circle"></i> Confirmar
                                    </a>
                                    <a href="revertir_preliquidacion.php?id=<?php echo $idPreliquidacion; ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('¿Revertir a estado pendiente? Se perderán todos los cálculos.')">
                                        <i class="bi bi-arrow-counterclockwise"></i> Revertir
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Listado de empleados (sin cambios) -->
                            <h3>Empleados en esta Preliquidación</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Empleado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($empleados as $empleado) { ?>
                                        <tr>
                                            <td><?php echo $empleado['nombre'] . " " . $empleado['apellido']; ?></td>
                                            <td>
                                                <a href="detalle_empleado.php?id=<?php echo $empleado['idempleado']; ?>&preliquidacion=<?php echo $idPreliquidacion; ?>" class="btn btn-info btn-sm">Ver Detalles</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <a href="Preliquidacion.php" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once 'partes/footer.php'; ?>
    <!-- Scripts sin cambios -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
</body>

</html>