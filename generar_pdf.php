<?php
require_once('TCPDF-main/tcpdf.php');

function generarReportePDF($licencias_data, $licencia_por_cargo, $fechaInicio, $fechaFin, $grafico_img_cargo = null, $grafico_img_lineal = null, $empleados_con_licencias = [], $total_dias_periodo = 0, $empleado_top = null)
{
    // 1. Validar que recibimos los gráficos
    if (empty($_POST['grafico_img_cargo'])) {
        file_put_contents('pdf_error.log', "No se recibió el gráfico de cargos\n", FILE_APPEND);
        die("Error: No se recibieron los datos del gráfico. Verifica la consola del navegador.");
    }

    // 2. Limpiar buffers de salida
    while (ob_get_level()) {
        ob_end_clean();
    }

    // 3. Crear instancia TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Casa Borras');
    $pdf->SetTitle('Reporte Estadístico de Licencias');
    $pdf->SetMargins(15, 25, 15);
    $pdf->AddPage();
    $pdf->Image('assets/img/LOGO2.jpg', 95, 0, 0, 0);

    // 4. Función mejorada para insertar imágenes
    function insertarImagenPDF($pdf, $imgData, $titulo) {
        try {
            // Extraer datos base64
            $imgData = preg_replace('/^data:image\/(png|jpeg);base64,/', '', $imgData);
            $imgBinary = base64_decode($imgData);
            
            if ($imgBinary === false) {
                throw new Exception("Error al decodificar imagen base64");
            }

            // Guardar temporalmente para depuración
            file_put_contents('debug_chart.png', $imgBinary);

            // Agregar título
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, $titulo, 0, 1, 'C');
            $y = $pdf->GetY();

            // Insertar imagen directamente desde los datos binarios
            $pdf->Image('@'.$imgBinary, 15, $y, 180, 0, 'PNG', '', '', false, 300, '', false, false, 0);
            
            // Calcular nueva posición Y (altura aproximada 120)
            $pdf->SetY($y + 100);
            
            return true;
        } catch (Exception $e) {
            file_put_contents('pdf_error.log', "Error al insertar imagen: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    // 5. Contenido del PDF
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, "Reporte Estadístico de Licencias", 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, "Período: $fechaInicio a $fechaFin", 0, 1, 'C');
    $pdf->Ln(10);

    // Tabla Licencias por Cargo
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Licencias por Cargo', 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 7, 'Cargo', 1);
    $pdf->Cell(50, 7, 'Tipo de Licencia', 1);
    $pdf->Cell(30, 7, 'Estado', 1);
    $pdf->Cell(30, 7, 'Total Licencias', 1);
    $pdf->Cell(30, 7, 'Total Días', 1);
    $pdf->Ln();

    $pdf->SetFont('helvetica', '', 10);
    foreach ($licencia_por_cargo as $row) {
        $pdf->Cell(50, 6, $row['cargo'], 1);
        $pdf->Cell(50, 6, $row['tipo_licencia'], 1);
        $pdf->Cell(30, 6, $row['estado'], 1);
        $pdf->Cell(30, 6, $row['total_licencias'], 1);
        $pdf->Cell(30, 6, $row['total_dias'], 1);
        $pdf->Ln();
    }

    $pdf->Ln(10);

   
    // Insertar gráficos
    insertarImagenPDF($pdf, $_POST['grafico_img_cargo'], 'Licencias por Cargo');
 // Empleado top
    if ($empleado_top) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Empleado con más licencias:', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 8, "{$empleado_top['nombre']} {$empleado_top['apellido']} ({$empleado_top['total_licencias']} licencia, {$empleado_top['total_dias']} días)", 0, 1);
        $pdf->Ln(5);
    }
    // Tabla empleados con licencias
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Empleados que tomaron licencias', 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 7, 'Nombre', 1);
    $pdf->Cell(40, 7, 'Apellido', 1);
    $pdf->Cell(50, 7, 'Cargo', 1);
    $pdf->Cell(40, 7, '% Días de Licencia', 1);
    $pdf->Ln();

    $pdf->SetFont('helvetica', '', 10);
    foreach ($empleados_con_licencias as $emp) {
        $porcentaje = $total_dias_periodo > 0 ? round(($emp['total_dias'] / $total_dias_periodo) * 100, 2) : 0;
        $pdf->Cell(40, 6, $emp['nombre'], 1);
        $pdf->Cell(40, 6, $emp['apellido'], 1);
        $pdf->Cell(50, 6, $emp['cargo'], 1);
        $pdf->Cell(40, 6, $porcentaje . '%', 1);
        $pdf->Ln();
    }
    insertarImagenPDF($pdf, $_POST['grafico_img_lineal'], 'Evolución Mensual de Licencias');

    // ... (resto de tu código para tablas y contenido)
    

    

    // 6. Salida del PDF
    $pdf->Output('reporte_licencias.pdf', 'I');
    exit;
}