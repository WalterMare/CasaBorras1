<?php
require_once('TCPDF-main/tcpdf.php');

function generarReportePDF($licencias_data)
{
    

    ob_start(); // Inicia el almacenamiento en búfer de salida
    ini_set('display_errors', 0); // Desactiva la visualización de errores
    error_reporting(0);

    // Crear objeto TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configuración de la página
    $pdf->AddPage();
    $pdf->Image('assets/img/LOGO2.jpg', 90, 0, 20, 20, 'JPG'); // Logo

    // Título
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 30, 'Reporte Estadístico de Licencias', 0, 1, 'C');

    // Espaciado antes de la tabla
    $pdf->Ln(20);
    
    // Definir encabezados de la tabla
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 10, 'Tipo de Licencia', 1);
    $pdf->Cell(40, 10, 'Estado', 1);
    $pdf->Cell(50, 10, 'Total Licencias', 1);
    $pdf->Cell(40, 10, 'Total Días', 1);
    $pdf->Ln();

    // Datos de la tabla
    $pdf->SetFont('helvetica', '', 10);
    foreach ($licencias_data as $data) {
        $pdf->Cell(50, 10, $data['tipo_licencia'], 1);
        $pdf->Cell(40, 10, $data['estado'], 1);
        $pdf->Cell(50, 10, $data['total_licencias'], 1);
        $pdf->Cell(40, 10, $data['total_dias'], 1);
        $pdf->Ln();
    }

    // Calcular totales
    $totalLicencias = array_sum(array_column($licencias_data, 'total_licencias'));
    $totalDias = array_sum(array_column($licencias_data, 'total_dias'));
    
    // Mostrar los totales
    $pdf->Cell(90, 10, 'Totales', 1, 0, 'C');
    $pdf->Cell(50, 10, number_format(round($totalLicencias, 2), 2), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format(round($totalDias, 2), 2), 1, 1, 'C');

    // Salida del PDF
    $pdf->Output('reporte_estadistico_licencias.pdf', 'D');
    
    ob_clean(); // Limpia el búfer de salida
    exit;
}


