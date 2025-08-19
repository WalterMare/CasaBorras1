<?php
require_once('TCPDF-main/tcpdf.php');

// Creamos una clase que extiende TCPDF para personalizar el Footer
class PDFConFooter extends TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

function generarReportePDF(
    $grafico_img_cargo = null,
    $grafico_img_lineal = null,
    $grafico_img_pago = null,
    $fechaInicio = '',
    $fechaFin = '',
    $empleado_top = null
) {
    // Limpiar buffers de salida
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Usamos nuestra clase con footer personalizado
    $pdf = new PDFConFooter(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configuración general
    $pdf->SetCreator('Casa Borras');
    $pdf->SetAuthor('Casa Borras');
    $pdf->SetTitle('Reporte Estadístico de Licencias');
    $pdf->SetMargins(20, 25, 20);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(15);
    $pdf->SetAutoPageBreak(true, 25);

    $pdf->AddPage();

    // Logo centrado
    $pdf->Image('assets/img/LOGO2.jpg', 85, 5, 40, 0, '', '', 'T', false, 300);

    // Espacio después del logo
    $pdf->Ln(35);

    // Encabezado principal
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, "Reporte Estadístico de Licencias", 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, "Período: $fechaInicio a $fechaFin", 0, 1, 'C');
    $pdf->Ln(0);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY()); // línea separadora
    $pdf->Ln(4);
    // Función para insertar gráficos
    $insertarImagenPDF = function ($pdf, $imgData, $titulo) {
        if (!$imgData || !is_string($imgData)) return false;
        try {
            $imgData = preg_replace('/^data:image\/(png|jpeg);base64,/', '', $imgData);
            $imgBinary = base64_decode($imgData);
            if ($imgBinary === false) return false;

            // Título del gráfico
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, $titulo, 0, 1, 'C');
            $pdf->Ln(-10);

            // Imagen centrada
            $pdf->Image('@' . $imgBinary, 50, $pdf->GetY(), 105, 0, 'PNG', '', 'C', false, 200);
            $pdf->Ln(105); // espacio debajo del gráfico
            $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY()); // línea separadora
            $pdf->Ln(5);

            return true;
        } catch (Exception $e) {
            file_put_contents('pdf_error.log', "Error al insertar imagen: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    };

    // Insertar gráficos
    $insertarImagenPDF($pdf, $grafico_img_cargo, 'Licencias por Cargo');
    $insertarImagenPDF($pdf, $grafico_img_pago, 'Ausentismo Pago por Tipo');
    $insertarImagenPDF($pdf, $grafico_img_lineal, 'Evolución Mensual de Licencias');

    // Salida del PDF
    $pdf->Output('reporte_licencias.pdf', 'I');
    exit;
}
