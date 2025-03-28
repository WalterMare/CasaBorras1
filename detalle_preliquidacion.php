<?php
session_start();
require_once 'conexiondb.php';
$conexion = ConexionBD();

if (!isset($_GET['id'])) {
    die("Error: No se proporcionó una preliquidación válida.");
}

$idPreliquidacion = intval($_GET['id']);

// Función para obtener detalles de la preliquidación
function Obtener_Detalles_Preliquidacion($vConexion, $idPreliquidacion)
{
    $Detalles = array();

    $consulta = "SELECT 
                    p.fecha, 
                    ep.descripcionPreliquidacion AS estado
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
    $Detalles = mysqli_fetch_assoc($result);

    return $Detalles;
}

// Función para obtener empleados asociados a una preliquidación
function Listar_Empleados_Preliquidacion($vConexion, $idPreliquidacion)
{
    $Listado = array();

    $consulta = "SELECT 
                    e.idempleado, 
                    e.nombre, 
                    e.apellido
                FROM 
                    detallepreliquidacion dp
                JOIN 
                    empleado e ON dp.idEmpleado = e.idempleado
                WHERE 
                    dp.idPreliquidacion = ?";

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
            <h1> Preliquidaciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Gestor de Reportes</li>
                    <li class="breadcrumb-item active">Preliquidaciones</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h2>Detalles de Preliquidación</h2>
                            <p><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($preliquidacion['fecha'])); ?></p>
                            <p><strong>Estado:</strong> <?php echo $preliquidacion['estado']; ?></p>

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

    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
</body>

</html>