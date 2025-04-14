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
                    dp.tiposSanciones AS tipoSancion, 
                    dp.tiposLicencias AS tipoLicencia
                FROM 
                    detallepreliquidacion dp
                INNER JOIN 
                    empleado e ON dp.idEmpleado = e.idempleado
                LEFT JOIN
                    cargo c ON e.idCargo = c.idcargo
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

                            <!-- Encabezado de Detalles del Empleado -->
                            <div class="mb-4 p-3 bg-light border rounded">
                                <h4 class="mb-2">Detalles de <strong><?php echo $detalle['nombre'] . " " . $detalle['apellido']; ?></strong></h4>
                                <p class="mb-1"><strong>DNI:</strong> <?php echo $detalle['dni']; ?></p>
                                <p class="mb-1"><strong>Cargo:</strong> <?php echo $detalle['cargo']; ?></p>
                            </div>

                            <!-- Contenido distribuido en tarjetas -->
                            <div class="row g-3">

                                <!-- Horas Extras -->
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white"><i class="fas fa-clock"></i> Horas Extras</div>
                                        <div class="card-body">
                                            <p><strong>Horas Extras:</strong> <?php echo $detalle['idHorasExtras'] > 0 ? $detalle['idHorasExtras'] . " horas" : "No Registra"; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Licencias -->
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark"><i class="fas fa-file-medical"></i> Licencias</div>
                                        <div class="card-body">
                                            <p><strong>Licencias:</strong> <?php echo $detalle['idLicencia'] > 0 ? $detalle['idLicencia'] . " días" : "No Registra"; ?></p>
                                            <p><strong>Tipos de Licencias:</strong> <?php echo $detalle['tipoLicencia'] ?? 'No Registra'; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Movimientos Económicos -->
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white"><i class="fas fa-dollar-sign"></i> Movimientos Económicos</div>
                                        <div class="card-body">
                                            <p><strong>Anticipos:</strong> $<?php echo number_format($detalle['idAnticipo'], 2); ?></p>
                                            <p><strong>Obra Social:</strong> <?php echo $detalle['idObraSocial'] ?? 'No Registra'; ?></p>
                                            <p><strong>Viáticos:</strong> $<?php echo number_format($detalle['idViatico'], 2); ?></p>
                                            <p><strong>Embargos:</strong> $<?php echo number_format($detalle['idEmbargo'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sanciones -->
                                <div class="col-md-6">
                                    <div class="card border-danger">
                                        <div class="card-header bg-danger text-white"><i class="fas fa-exclamation-triangle"></i> Sanciones</div>
                                        <div class="card-body">
                                            <p><strong>Sanción:</strong>
                                                <?php
                                                $tipo = strtolower(trim($detalle['tipoSancion']));
                                                if (($tipo === 'apercibimiento verbal' || $tipo === 'apercibimiento escrito') && $detalle['idSancion'] == 0) {
                                                    echo "Solo notificación";
                                                } elseif ($detalle['idSancion'] > 0) {
                                                    echo $detalle['idSancion'] . " días";
                                                } elseif (!empty($tipo)) {
                                                    echo ucfirst($tipo);
                                                } else {
                                                    echo "No Registra";
                                                }
                                                ?>
                                            </p>
                                            <p><strong>Tipos de Sanciones:</strong> <?php echo $detalle['tipoSancion'] ?? 'No Registra'; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jornada -->
                                <div class="col-md-6">
                                    <div class="card border-secondary">
                                        <div class="card-header bg-secondary text-white"><i class="fas fa-calendar-check"></i> Jornada</div>
                                        <div class="card-body">
                                            <p><strong>Días Trabajados:</strong> <?php echo $detalle['diasTrabajados']; ?></p>
                                        </div>
                                    </div>
                                </div>

                            </div>


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