<?php
session_start();

// Si no hay sesión activa, redirige al login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once 'conexiondb.php';
require_once 'Reporte_Estadistico_licencia_pdf.php';  // El archivo con la función para generar el reporte

// Conexión a la base de datos
$MiConexion = ConexionBD();

// Verificar si las fechas fueron enviadas
if (isset($_POST['fechainicio']) && isset($_POST['fechafin'])) {
    $fechaInicio = $_POST['fechainicio'];
    $fechaFin = $_POST['fechafin'];

    // Consultar las licencias
    $licencias_data = Consultar_licencias_para_reporte_estadistico($MiConexion, $fechaInicio, $fechaFin);

    // Si existen datos, generar el PDF
    if ($licencias_data) {
        generarReportePDF($licencias_data);  // Función que genera el PDF
    } else {
        echo "No se encontraron licencias en esas fechas.";
    }
} else {
    echo "Por favor, ingresa un rango de fechas.";
}

// Función para generar el reporte en PDF
function generarReportePDF($licencias_data)
{
    require_once('TCPDF-main/tcpdf.php');
    // Verificar si TCPDF está cargado correctamente
    if (!class_exists('TCPDF')) {
        die('TCPDF no está instalado o no se puede acceder a él.');
    }
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();

    // Establecer el título del reporte
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 15, 'Reporte Estadístico de Licencias', 0, 1, 'C');

    // Graficar los datos de licencias por tipo
    $licencia_tipos = [];
    foreach ($licencias_data as $data) {
        $licencia_tipos[$data['tipo_licencia']][$data['estado']] = $data['total_licencias'];
    }

    // Preparar el gráfico de barras
    $chart_width = 100;  // Ancho del gráfico
    $chart_height = 60;  // Altura del gráfico

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Licencias por Tipo y Estado', 0, 1, 'L');

    // Crear el gráfico de barras para cada tipo de licencia
    foreach ($licencia_tipos as $tipo => $estados) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Tipo de Licencia: $tipo", 0, 1, 'L');

        // Crear las barras para cada estado
        $max_height = max($estados);
        $bar_width = $chart_width / count($estados);
        $x = $pdf->GetX();

        foreach ($estados as $estado => $total) {
            $bar_height = ($total / $max_height) * $chart_height;
            $pdf->SetXY($x, $pdf->GetY());
            $pdf->Rect($x, $pdf->GetY(), $bar_width, -$bar_height, 'DF', ['fill' => [100, 150, 255]]);  // Barra azul
            $x += $bar_width;
        }

        // Saltar una línea después de cada gráfico
        $pdf->Ln($chart_height + 10);
    }

    // Mostrar la tabla de estadísticas
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 10, 'Tipo de Licencia', 1);
    $pdf->Cell(40, 10, 'Estado', 1);
    $pdf->Cell(40, 10, 'Total Licencias', 1);
    $pdf->Cell(40, 10, 'Total Días', 1);
    $pdf->Ln();

    // Establecer el contenido de la tabla
    $pdf->SetFont('helvetica', '', 10);
    foreach ($licencias_data as $data) {
        $pdf->Cell(40, 10, $data['tipo_licencia'], 1);
        $pdf->Cell(40, 10, $data['estado'], 1);
        $pdf->Cell(40, 10, $data['total_licencias'], 1);
        $pdf->Cell(40, 10, $data['total_dias'], 1);
        $pdf->Ln();
    }

    // Generar el PDF
    $pdf->Output('reporte_estadistico_licencias.pdf', 'I');
}
