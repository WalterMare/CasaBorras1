<?php
session_start();
require_once 'conexiondb.php';
$conexion = ConexionBD();

if (!isset($_GET['id']) || !isset($_GET['preliquidacion'])) {
    die("Error: Datos insuficientes.");
}

$idEmpleado = intval($_GET['id']);
$idPreliquidacion = intval($_GET['preliquidacion']);

function Obtener_Detalles_Empleado($vConexion, $idEmpleado, $idPreliquidacion)
{
    $consulta = "SELECT 
                    e.nombre, e.apellido, e.dni, 
                    c.descripcion AS cargo,  
                    dp.idHorasExtras, dp.idLicencia, dp.idAnticipo, 
                    dp.idObraSocial, dp.idFamiliar, dp.idSancion, 
                    dp.idEmbargo, dp.idViatico,
                    dp.diasTrabajados,
                    ts.nombreTipo AS tipoSancion, 
                    tl.descripcion AS tipoLicencia
                FROM 
                    detallepreliquidacion dp
                INNER JOIN 
                    empleado e ON dp.idEmpleado = e.idempleado
                LEFT JOIN
                    cargo c ON e.idCargo = c.idcargo
                LEFT JOIN
                    tiposancion ts ON dp.tiposSanciones = ts.idtipoSancion
                LEFT JOIN
                    tipolicencia tl ON dp.tiposLicencias = tl.idtipoLicencia
                WHERE 
                    dp.idEmpleado = ? AND dp.idPreliquidacion = ?";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "ii", $idEmpleado, $idPreliquidacion);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}



$detalle = Obtener_Detalles_Empleado($conexion, $idEmpleado, $idPreliquidacion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Detalles del Empleado</title>

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
                    <li class="breadcrumb-item active">Detalle Preliquidación</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                        <h2>Detalles de <?php echo $detalle['nombre'] . " " . $detalle['apellido']; ?></h2>
                            <p><strong>DNI:</strong> <?php echo $detalle['dni']; ?></p>
                            <p><strong>Cargo:</strong> <?php echo $detalle['cargo']; ?></p>

                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Horas Extras</strong></td>
                                        <td><?php echo $detalle['idHorasExtras'] > 0 ? $detalle['idHorasExtras'] . " horas" : "No Registra"; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Licencias</strong></td>
                                        <td><?php echo $detalle['idLicencia'] > 0 ? $detalle['idLicencia'] . " días" : "No Registra"; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipos de Licencias</strong></td>
                                        <td><?php echo $detalle['tipoLicencia'] ?? 'No Registra'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Anticipos</strong></td>
                                        <td>$<?php echo number_format($detalle['idAnticipo'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Obra Social</strong></td>
                                        <td><?php echo $detalle['idObraSocial'] ?? 'No Registra'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Viáticos</strong></td>
                                        <td>$<?php echo number_format($detalle['idViatico'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Embargos</strong></td>
                                        <td>$<?php echo number_format($detalle['idEmbargo'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sanción</strong></td>
                                        <td><?php echo $detalle['idSancion'] > 0 ? $detalle['idSancion'] . " días" : "No Registra"; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipos de Sanciones</strong></td>
                                        <td><?php echo $detalle['tipoSancion'] ?? 'No Registra'; ?></td>
                                    </tr>
                                    
                                    <tr>
                                        <td><strong>Días Trabajados</strong></td>
                                        <td><?php echo $detalle['diasTrabajados']; ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <a href="detalle_preliquidacion.php?id=<?php echo $idPreliquidacion; ?>" class="btn btn-secondary">Volver</a>
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