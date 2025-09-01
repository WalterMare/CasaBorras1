<?php
session_start();

// Verificar si la sesión está activa
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
$Cantidad = 0; // Para el número de resultados obtenidos
$idLicencia = 0;
// Incluir la conexión a la base de datos
require_once 'conexiondb.php';
$conexion = ConexionBD();

// Obtener los parámetros de búsqueda desde el formulario
$empleado_id = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
$tipo_reporte = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : '';


require_once 'Select_reportes.php';
$listado = Listar_Reporte_Empleado3($conexion, $empleado_id, $tipo_reporte);
$Cantidad = count($listado);

$listadoEmbargo = Listar_Reporte_Empleado_Embargo($conexion, $empleado_id);
$CantidadEmbargo = count($listadoEmbargo);

$listadoSancion = Listar_Reporte_Empleado_Sancion($conexion, $empleado_id);
$CantidadSancion = count($listadoSancion);

$listadoHorasExtras = Listar_Reporte_Empleado_HorasExtras($conexion, $empleado_id);
$Cantidadhorasextras = count($listadoHorasExtras);

$listadoViaticos = Listar_Reporte_Viaticos_Empleado($conexion, $empleado_id);
$CantidadViaticos = count($listadoViaticos);

$listadoVacaciones = Listar_Reporte_Empleado_Vacaciones($conexion, $empleado_id);
$CantidadVacaciones = count($listadoVacaciones);

$reporte = Listar_Reporte_Asistencias_Empleado($conexion, $empleado_id);
$listadoAsistencia = $reporte['asistencias'];
$totalHoras = $reporte['total_horas'];
$CantidadAsistencia = count($listadoAsistencia);

$listadoDetalle = 0;

require_once 'select_empleado.php';
$empleado = Listar_empleadoId($conexion, $empleado_id);

require_once('TCPDF-main/tcpdf.php');


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Consulta de Licencias</title>
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
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1> Reportes</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Reporte Generado</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Reporte del Empleado:</h5>

                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Empleado</th>
                                        <th scope="col">D.N.I</th>
                                        <th scope="col">Sexo</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Inicio de Actividad</th>
                                        <th scope="col">Cargo</th>
                                        <th scope="col">Ciudad</th>
                                        <th scope="col">Provincia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($empleado)) { ?>
                                        <tr>
                                            <td><?php echo $empleado['NOMBRE'] . " " . $empleado['APELLIDO']; ?></td>
                                            <td><?php echo $empleado['DNI']; ?></td>
                                            <td><?php echo $empleado['SEXO']; ?></td>
                                            <td><?php echo $empleado['ESTADO']; ?></td>
                                            <td><?php echo $empleado['FECHAINICIO']; ?></td>
                                            <td><?php echo $empleado['CARGO']; ?></td>
                                            <td><?php echo $empleado['CIUDAD']; ?></td>
                                            <td><?php echo $empleado['PROVINCIA']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section> <!-- End Default Table Example --> <!-- Default Table -->
        <section class="section">
            <?php if ($Cantidad != 0 && $Cantidad != null && $tipo_reporte == '1') { ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Licencias con Detalles</h5>
                                <?php if (!empty($listado)): ?>
                                    <?php $contadorLicencias = 1; ?>
                                    <?php foreach ($listado as $licencia): ?>
                                        <!-- Encabezado de la licencia -->
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                Licencia <?= $contadorLicencias; ?>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Tabla principal de la licencia -->
                                                <table class="table table-striped mb-2">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha Inicio</th>
                                                            <th>Fecha Fin</th>
                                                            <th>Días</th>
                                                            <th>Tipo</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><?= $licencia['FECHAINICIO']; ?></td>
                                                            <td><?= $licencia['FECHAFIN']; ?></td>
                                                            <td><?= $licencia['CANTIDADDIAS']; ?></td>
                                                            <td><?= $licencia['TIPO']; ?></td>
                                                            <td><?= $licencia['ESTADO']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!-- Detalles de la licencia -->
                                                <?php
                                                $listadoDetalles = Listar_Reporte_Empleado3_Detalle($conexion, $licencia['ID']);
                                                ?>
                                                <?php if (!empty($listadoDetalles) && !isset($listadoDetalles['mensaje'])): ?>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Descripción</th>
                                                                <th>Fecha de Creación</th>
                                                                <th>Usuario Otorga</th>
                                                                <th>Documentación</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($listadoDetalles as $j => $detalle): ?>
                                                                <tr>
                                                                    <th><?= $j + 1; ?></th>
                                                                    <td><?= $detalle['DESCRIPCION']; ?></td>
                                                                    <td><?= $detalle['FECHACREACION']; ?></td>
                                                                    <td><?= $detalle['USUARIO']; ?></td>
                                                                    <td>
                                                                        <a href="download_document.php?file_id=<?= $detalle['IDDETALLELICENCIA']; ?>" class="btn btn-link">
                                                                            Descargar Documentación
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                <?php else: ?>
                                                    <p class="text-muted mb-0">No hay detalles disponibles para esta licencia.</p>
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                        <?php $contadorLicencias++; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">El empleado seleccionado no tiene licencias.</p>
                                <?php endif; ?>

                            </div>
                            <div class="d-flex justify-content-end pe-3">
                                <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                    <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                    <input type="hidden" name="tipo_reporte" value="1"> <!-- Agregar el parámetro adicional -->
                                    <input type="hidden" name="id_licencia" value="<?php echo $idLicencia; ?>">
                                    <button type="submit" class="btn btn-primary" <?php echo $listadoDetalles == 0 ? 'disabled' : ''; ?>>Generar PDF</button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } else if ($Cantidad == 0 && $Cantidad == null && $tipo_reporte == '1') { ?>
                <div class="alert alert-warning">No hay licencias disponibles para este empleado.</div>
            <?php } ?>
        </section>
        <?php
        if ($tipo_reporte == '6') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Embargos</h5>
                                <?php if (!empty($listadoEmbargo)) { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Expediente</th>
                                                <th>Tipo</th>
                                                <th>Fecha</th>
                                                <th>Inicio</th>
                                                <th>Fin</th>
                                                <th>Estado</th>
                                                <th>Monto</th>
                                                <th>%</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($listadoEmbargo as $index => $embargo) { ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><?= htmlspecialchars($embargo['EXPEDIENTE']) ?></td>
                                                    <td><?= htmlspecialchars($embargo['TIPO']) ?></td>
                                                    <td><?= $embargo['FECHA'] ?></td>
                                                    <td><?= $embargo['FECHA_INICIO'] ?></td>
                                                    <td><?= $embargo['FECHA_FIN'] ?></td>
                                                    <td><?= ($embargo['ESTADO'] == 1) ? 'Activo' : 'Finalizado' ?></td>
                                                    <td>$<?= number_format($embargo['MONTO'], 2) ?></td>
                                                    <td><?= $embargo['PORCENTAJE'] ?>%</td>
                                                    <td><?= htmlspecialchars($embargo['DESCRIPCION']) ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } else {
                                    echo "<p>No se encontraron registros de embargos.</p>";
                                } ?>
                                <div class="d-flex justify-content-end pe-3">
                                    <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                        <input type="hidden" name="empleado" value="<?= $empleado['ID'] ?>">
                                        <input type="hidden" name="tipo_reporte" value="6">
                                        <button type="submit" class="btn btn-primary">Generar PDF</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


            </section>
        <?php } ?>

        <?php
        if ($tipo_reporte == '2') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Sanciones</h5>
                                <?php if ($CantidadSancion != 0 && $CantidadSancion != null) { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Fecha de Inicio</th>
                                                <th scope="col">Fecha Fin</th>
                                                <th scope="col">Cantidad de Días</th>
                                                <th scope="col">Tipo de Sanción</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Descripción</th>
                                            </tr>
                                        </thead>

                                        <?php if ($CantidadSancion != 0 && $CantidadSancion != null) { ?>
                                            <tbody>
                                                <?php for ($i = 0; $i < $CantidadSancion; $i++) { ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $i + 1; ?></th>
                                                        <td><?php echo $listadoSancion[$i]['FECHA_INICIO']; ?></td>
                                                        <td><?php echo $listadoSancion[$i]['FECHA_FIN']; ?></td>
                                                        <td><?php echo $listadoSancion[$i]['CANTIDAD_DIAS']; ?></td>
                                                        <td><?php echo $listadoSancion[$i]['TIPO']; ?></td>
                                                        <td><?php echo $listadoSancion[$i]['ESTADO']; ?></td>
                                                        <td><?php echo $listadoSancion[$i]['DESCRIPCION']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                <?php } else {
                                    echo "No se encontraron registros";
                                } ?> <!-- End Default Table Example -->
                                <div class="d-flex justify-content-end pe-3">
                                    <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                        <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                        <input type="hidden" name="tipo_reporte" value="2">

                                        <button type="submit" class="btn btn-primary">Generar PDF</button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </section>
        <?php } ?>
        <?php
        if ($tipo_reporte == '3') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Horas Extras</h5>
                                <?php if (!empty($listadoHorasExtras)) : ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Fecha</th>
                                                <th scope="col">Hora Inicio</th>
                                                <th scope="col">Cantidad de Horas</th>
                                                <th scope="col">Tipo de Hora</th>
                                                <th scope="col">Recargo</th>
                                                <th scope="col">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($listadoHorasExtras as $index => $horaExtra) : ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><?= htmlspecialchars($horaExtra['FECHA']) ?></td>
                                                    <td><?= htmlspecialchars($horaExtra['HORA_INICIO']) ?></td>
                                                    <td><?= htmlspecialchars($horaExtra['CANTIDAD_HORAS']) ?></td>
                                                    <td><?= htmlspecialchars($horaExtra['TIPO_HORA']) ?></td>
                                                    <td><?= htmlspecialchars($horaExtra['TIPO_RECARGO']) ?></td>
                                                    <td>$ <?= number_format($horaExtra['VALOR_HORA_EXTRA'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p>No se encontraron registros</p>
                                <?php endif; ?>

                                <div class="d-flex justify-content-end pe-3">
                                    <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                        <input type="hidden" name="empleado" value="<?= htmlspecialchars($empleado['ID']) ?>">
                                        <input type="hidden" name="tipo_reporte" value="3">
                                        <button type="submit" class="btn btn-primary">Generar PDF</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </section>
        <?php } ?>
        <?php
        if ($tipo_reporte == '5') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Viaticos</h5>
                                <?php if ($CantidadViaticos != 0 && $CantidadViaticos != null) { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Fecha</th>
                                                <th scope="col">Tipo</th>
                                                <th scope="col">Monto</th>

                                            </tr>
                                        </thead>

                                        <?php if ($CantidadViaticos != 0 && $CantidadViaticos != null) { ?>
                                            <tbody>
                                                <?php for ($i = 0; $i < $CantidadViaticos; $i++) { ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $i + 1; ?></th>
                                                        <td><?php echo $listadoViaticos[$i]['FECHA_OTORGAMIENTO']; ?></td>
                                                        <td><?php echo $listadoViaticos[$i]['TIPO_VIATICO']; ?></td>
                                                        <td><?php echo $listadoViaticos[$i]['MONTO']; ?></td>

                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                <?php } else {
                                    echo "No se encontraron registros";
                                } ?> <!-- End Default Table Example -->
                                <div class="d-flex justify-content-end pe-3">
                                    <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                        <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                        <input type="hidden" name="tipo_reporte" value="5">
                                        <button type="submit" class="btn btn-primary">Generar PDF</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </section>
        <?php } ?>
        <?php
        if ($tipo_reporte == '4') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Asistencia</h5>

                                <?php if ($CantidadAsistencia > 0) { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Fecha</th>
                                                <th scope="col">Hora de Entrada</th>
                                                <th scope="col">Hora de Salida</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($i = 0; $i < $CantidadAsistencia; $i++) { ?>
                                                <tr>
                                                    <th scope="row"><?php echo $i + 1; ?></th>
                                                    <td><?php echo $listadoAsistencia[$i]['FECHA']; ?></td>
                                                    <td><?php echo $listadoAsistencia[$i]['HORA_ENTRADA']; ?></td>
                                                    <td><?php echo $listadoAsistencia[$i]['HORA_SALIDA']; ?></td>
                                                    <td><?php echo $listadoAsistencia[$i]['ESTADO']; ?></td>
                                                    <td><?php echo $listadoAsistencia[$i]['OBSERVACIONES']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>

                                    <!-- Mostrar Total de Horas Trabajadas -->
                                    <p><strong>Total de horas trabajadas en el mes actual:</strong> <?php echo $totalHoras; ?></p>

                                <?php } else {
                                    echo "<p>No se encontraron registros</p>";
                                } ?>

                                <!-- Botón para Generar PDF -->
                                <div class="d-flex justify-content-end pe-3">
                                    <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                        <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                        <input type="hidden" name="tipo_reporte" value="4">
                                        <button type="submit" class="btn btn-primary">Generar PDF</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <?php
        if ($tipo_reporte == '7') { ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Vacaciones</h5>
                                <h5 class="card-title">Vacaciones</h5>
                                <?php if (!empty($listadoVacaciones)) { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Fecha Inicio</th>
                                                <th scope="col">Fecha Fin</th>
                                                <th scope="col">Cantidad de días</th>
                                                <th scope="col">Año</th>
                                                <th scope="col">Días restantes</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Observaciones</th>
                                                <th scope="col">Fecha Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($listadoVacaciones as $index => $vac) { ?>
                                                <tr>
                                                    <th scope="row"><?php echo $index + 1; ?></th>
                                                    <td><?php echo $vac['FECHA_INICIO']; ?></td>
                                                    <td><?php echo $vac['FECHA_FIN']; ?></td>
                                                    <td><?php echo $vac['CANTIDAD_DIAS']; ?></td>
                                                    <td><?php echo $vac['AÑO']; ?></td>
                                                    <td><?php echo $vac['VACACIONES_RESTANTES']; ?></td>
                                                    <td>
                                                        <?php
                                                        // Mostrar el estado con color
                                                        switch ($vac['ESTADO']) {
                                                            case 'Aprobado':
                                                                echo '<span class="badge bg-success">Aprobado</span>';
                                                                break;
                                                            case 'Pendiente':
                                                                echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                                                                break;
                                                            case 'Rechazado':
                                                                echo '<span class="badge bg-danger">Rechazado</span>';
                                                                break;
                                                            default:
                                                                echo $vac['ESTADO'];
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo !empty($vac['OBSERVACIONES']) ? $vac['OBSERVACIONES'] : '-'; ?></td>
                                                    <td><?php echo $vac['FECHA_REGISTRO']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } else {
                                    echo "<p>No se encontraron registros.</p>";
                                } ?>
                                <div class="d-flex justify-content-end pe-3">
                                    <form action="generar_pdf_Reporte_Licencia.php" method="get">
                                        <input type="hidden" name="empleado" value="<?php echo $empleado['ID']; ?>">
                                        <input type="hidden" name="tipo_reporte" value="7">
                                        <button type="submit" class="btn btn-primary">Generar PDF</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <br>
        <div class="text-center">
            <a href="Reportes.php" class="text-primary fw-bold">Regresar</a>
        </div>
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