<?php
session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// Variables para el filtro de fechas
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';


// Convertir las fechas a formato de timestamp
$timestampInicio = strtotime($fechaInicio);
$timestampFin = strtotime($fechaFin);

// Calcular la diferencia en días
$diasTotales = ($timestampFin - $timestampInicio) / (60 * 60 * 24);


// Crear el arreglo de inasistencia
$datos_inasistencia = array();

// Verificamos que las fechas no estén vacías
if (!empty($fechaInicio) && !empty($fechaFin)) {
    // Consulta para obtener el total de ausencias por tipo
    $query_resumen = "
    SELECT tipo_ausencia, COUNT(*) AS total
    FROM (
        SELECT 'Vacaciones' AS tipo_ausencia FROM vacaciones WHERE estado = 'Aprobada' AND fecha_inicio BETWEEN ? AND ? 
        UNION ALL
        SELECT 'Sanción' FROM sancion WHERE fecha_inicio BETWEEN ? AND ? 
        UNION ALL
        SELECT 'Licencia' FROM licencia WHERE fechainicio BETWEEN ? AND ?
    ) AS ausencias
    GROUP BY tipo_ausencia";

    $stmt_resumen = mysqli_prepare($conexion, $query_resumen);
    mysqli_stmt_bind_param($stmt_resumen, 'ssssss', $fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin);
    mysqli_stmt_execute($stmt_resumen);
    $resultado_resumen = mysqli_stmt_get_result($stmt_resumen);

    // Consulta detallada de empleados inactivos (vacaciones, sanciones, licencias)
    $query_detalle = "
    SELECT 
        c.descripcion AS puesto,
        e.idempleado,
        e.nombre,
        e.apellido,
        COALESCE(v.fecha_inicio, s.fecha_inicio, l.fechainicio, NULL) AS fecha_inicio_ausencia,
        COALESCE(v.fecha_fin, DATE_ADD(s.fecha_inicio, INTERVAL s.cantidadDias DAY), l.fechafin, NULL) AS fecha_fin_ausencia,
        CASE 
            WHEN v.idvacaciones IS NOT NULL THEN 'Vacaciones'
            WHEN s.idsancion IS NOT NULL THEN 'Sanción'
            WHEN l.idlicencia IS NOT NULL THEN 'Licencia'
            ELSE 'Sin ausencias'
        END AS tipo_ausencia
    FROM empleado e
    JOIN cargo c ON e.Idcargo = c.idcargo
    LEFT JOIN vacaciones v ON e.idempleado = v.idempleado AND v.estado = 'Aprobada' 
        AND v.fecha_inicio BETWEEN ? AND ?
    LEFT JOIN sancion s ON e.idempleado = s.idEmpleado 
        AND s.fecha_inicio BETWEEN ? AND ?
    LEFT JOIN licencia l ON e.idempleado = l.idEmpleado 
        AND l.fechainicio BETWEEN ? AND ?
    WHERE v.idvacaciones IS NOT NULL OR s.idsancion IS NOT NULL OR l.idlicencia IS NOT NULL
    ORDER BY c.descripcion, e.idempleado, fecha_inicio_ausencia;
    ";

    $stmt_detalle = mysqli_prepare($conexion, $query_detalle);
    mysqli_stmt_bind_param($stmt_detalle, 'ssssss', $fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin);
    mysqli_stmt_execute($stmt_detalle);
    $resultado_detalle = mysqli_stmt_get_result($stmt_detalle);

    // Consulta para obtener la asistencia de los empleados
    $query_asistencia = "
    SELECT 
        e.idempleado,
        e.nombre,
        e.apellido,
        COUNT(a.idasistencia) AS cantidad_asistencias
    FROM empleado e
    LEFT JOIN asistencias a ON e.idempleado = a.idempleado
        AND a.fecha BETWEEN ? AND ?
    GROUP BY e.idempleado
    ORDER BY cantidad_asistencias DESC
    ";

    $stmt_asistencia = mysqli_prepare($conexion, $query_asistencia);
    mysqli_stmt_bind_param($stmt_asistencia, 'ss', $fechaInicio, $fechaFin);
    mysqli_stmt_execute($stmt_asistencia);
    $resultado_asistencia = mysqli_stmt_get_result($stmt_asistencia);

    // Consulta para obtener los días de inasistencia por empleado, incluyendo a los empleados sin registros de asistencia
    $query_inasistencia = "
SELECT 
    e.idempleado,
    e.nombre,
    e.apellido,
    COALESCE((
        SELECT COUNT(*)
        FROM asistencias a
        WHERE a.idEmpleado = e.idempleado
        AND a.fecha BETWEEN ? AND ?
        AND a.estado IN ('Presente', 'Tarde')
    ), 0) AS dias_presentes_tarde
FROM empleado e
ORDER BY dias_presentes_tarde";

    // Preparamos la consulta
    $stmt_inasistencia = mysqli_prepare($conexion, $query_inasistencia);

    // Vinculamos las fechas al query
    mysqli_stmt_bind_param($stmt_inasistencia, 'ss', $fechaInicio, $fechaFin);
    mysqli_stmt_execute($stmt_inasistencia);

    // Obtenemos el resultado
    $resultado_inasistencia = mysqli_stmt_get_result($stmt_inasistencia);
}

// Función para generar el reporte en PDF
function generarPDF($fechaInicio, $fechaFin, $resultado_resumen, $resultado_detalle, $resultado_asistencia, $resultado_inasistencia, $diasTotales)
{
    // Al inicio del script
    ob_start();
    require_once 'TCPDF-main/tcpdf.php';
    // Crear el objeto TCPDF
    $pdf = new TCPDF();
    // Crear el objeto TCPDF en formato horizontal
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // 'L' para landscape

    // Configuración del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Casa Borras');

    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 10);



    $pdf->AddPage();
    $pdf->Image('assets/img/LOGO2.jpg', 140, 0, 0, 0);
    // Fecha de la búsqueda (usando las fechas pasadas por GET)
    $fechaBusqueda = date('d/m/Y'); // Fecha actual de la búsqueda

    // Título del reporte
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 20, 'Reporte de Asistencia, Licencias, Vacaciones y Sanciones', 0, 1, 'C');
    $pdf->Ln(5);
    // Mostrar la fecha de la búsqueda
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 0, 'Fecha de Generación: ' . $fechaBusqueda, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Rango de Fechas: ' . $fechaInicio . ' al ' . $fechaFin, 0, 1, 'L');
    $pdf->Ln(5);

    // Resumen de Ausencias Justificadas
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Resumen de Ausencias Justificadas', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(40, 10, 'Tipo de Ausencia', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Total', 1, 1, 'C');

    while ($fila = mysqli_fetch_assoc($resultado_resumen)) {
        $pdf->Cell(40, 10, $fila['tipo_ausencia'], 1, 0, 'C');
        $pdf->Cell(40, 10, $fila['total'], 1, 1, 'C');
    }

    // Detalle de Empleados Inactivos
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Detalle de Empleados Inactivos', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(40, 10, 'Puesto', 1, 0, 'C');
    $pdf->Cell(40, 10, 'ID', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Nombre', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Fecha Inicio', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Fecha Fin', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Motivo', 1, 1, 'C');

    while ($fila = mysqli_fetch_assoc($resultado_detalle)) {
        $pdf->Cell(40, 10, $fila['puesto'], 1, 0, 'C');
        $pdf->Cell(40, 10, $fila['idempleado'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['nombre'] . ' ' . $fila['apellido'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['fecha_inicio_ausencia'] ?: 'N/A', 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['fecha_fin_ausencia'] ?: 'N/A', 1, 0, 'C');
        $pdf->Cell(40, 10, $fila['tipo_ausencia'], 1, 1, 'C');
    }

    // Asistencia de Empleados
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Asistencia de Empleados', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(40, 10, 'ID', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Nombre', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Apellido', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Cantidad de Asistencias', 1, 1, 'C');

    while ($fila = mysqli_fetch_assoc($resultado_asistencia)) {
        $pdf->Cell(40, 10, $fila['idempleado'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['nombre'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['apellido'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['cantidad_asistencias'], 1, 1, 'C');
    }

    // Días de Inasistencia de Empleados
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Días de Inasistencia de Empleados', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(40, 10, 'ID', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Nombre', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Apellido', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Días de Inasistencia', 1, 1, 'C');

  
    while ($fila = mysqli_fetch_assoc($resultado_inasistencia)) {
        $dias_inasistencia = $diasTotales - $fila['dias_presentes_tarde'];
        $pdf->Cell(40, 10, $fila['idempleado'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['nombre'], 1, 0, 'C');
        $pdf->Cell(50, 10, $fila['apellido'], 1, 0, 'C');
        $pdf->Cell(50, 10, $dias_inasistencia, 1, 1, 'C');
    }

    // Output the PDF
    $pdf->Output('reporte_asistencia.pdf', 'I');
}

if (isset($_POST['generar_pdf'])) {
    generarPDF($fechaInicio, $fechaFin, $resultado_resumen, $resultado_detalle, $resultado_asistencia, $resultado_inasistencia,$diasTotales);
    exit; // Para evitar que el contenido del HTML se muestre después del PDF
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
            <h1>Reporte de Asistencia, Licencias, Vacaciones y Sanciones</h1>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="mt-4">Buscar</h2>
                            <form method="get" class="my-3">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>Fecha Inicio:</label>
                                        <input type="date" class="form-control" name="fechaInicio" value="<?= $fechaInicio ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label>Fecha Fin:</label>
                                        <input type="date" class="form-control" name="fechaFin" value="<?= $fechaFin ?>" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary">Filtrar</button>
                                    </div>
                                </div>
                            </form>

                            <?php if (!empty($fechaInicio) && !empty($fechaFin)): ?>
                                <h2 class="mt-4">Resumen de Ausencias Justificadas</h2>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tipo de Ausencia</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($fila = mysqli_fetch_assoc($resultado_resumen)) { ?>
                                            <tr>
                                                <td><?= $fila['tipo_ausencia'] ?></td>
                                                <td><?= $fila['total'] ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <h2 class="mt-4">Detalle de Empleados que estuvieron Inactivos</h2>
                                <table class="table table-borderless">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th>Puesto</th>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Apellido</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($fila = mysqli_fetch_assoc($resultado_detalle)) { ?>
                                            <tr>
                                                <td><?= $fila['puesto'] ?></td>
                                                <td><?= $fila['idempleado'] ?></td>
                                                <td><?= $fila['nombre'] ?></td>
                                                <td><?= $fila['apellido'] ?></td>
                                                <td><?= $fila['fecha_inicio_ausencia'] ?: 'N/A' ?></td>
                                                <td><?= $fila['fecha_fin_ausencia'] ?: 'N/A' ?></td>
                                                <td><?= $fila['tipo_ausencia'] ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <h2 class="mt-4">Asistencia de Empleados</h2>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Apellido</th>
                                            <th scope="col">Cantidad de Asistencias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($fila = mysqli_fetch_assoc($resultado_asistencia)) { ?>
                                            <tr>
                                                <td><?= $fila['idempleado'] ?></td>
                                                <td><?= $fila['nombre'] ?></td>
                                                <td><?= $fila['apellido'] ?></td>
                                                <td><?= $fila['cantidad_asistencias'] ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <h2 class="mt-4">Cantidad de Días de Inasistencia por Empleado</h2>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Apellido</th>
                                            <th scope="col">Días de Inasistencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        // Recorremos los resultados de todos los empleados
                                        while ($empleado = mysqli_fetch_assoc($resultado_inasistencia)) {
                                            // Si el empleado tiene días registrados como presente o tarde, calculamos la inasistencia
                                            if ($empleado['dias_presentes_tarde'] > 0) {
                                                // Restamos los días presentes/tarde de los días totales
                                                $dias_inasistencia = $diasTotales - $empleado['dias_presentes_tarde'];
                                            } else {
                                                // Si no tiene registros, el total de días de inasistencia será igual a los días totales
                                                $dias_inasistencia = $diasTotales;
                                            }
                                            $datos_inasistencia[] = array(
                                                'idempleado' => $empleado['idempleado'],
                                                'nombre' => $empleado['nombre'],
                                                'apellido' => $empleado['apellido'],
                                                'dias_inasistencia' => $dias_inasistencia
                                            );

                                        ?>
                                            <tr>
                                                <td><?= $empleado['idempleado'] ?></td>
                                                <td><?= $empleado['nombre'] ?></td>
                                                <td><?= $empleado['apellido'] ?></td>
                                                <td><?= $dias_inasistencia ?></td>
                                            </tr>


                                        <?php } ?>
                                    </tbody>
                                </table>

                                <?php if (!empty($fechaInicio) && !empty($fechaFin)): ?>
                                    <form method="post">
                                        <button type="submit" name="generar_pdf" class="btn btn-danger mt-4">Generar PDF</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-center text-danger">Por favor, seleccione un rango de fechas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script> -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/vendor/echarts/echarts.min.js"></script>
<script src="assets/vendor/quill/quill.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>-->
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>

    <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>

<?php
// Cerrar conexiones
if ($resultado_resumen) mysqli_stmt_close($stmt_resumen);
if ($resultado_detalle) mysqli_stmt_close($stmt_detalle);
if ($resultado_asistencia) mysqli_stmt_close($stmt_asistencia);
mysqli_close($conexion);
?>