<?php
require_once('TCPDF-main/tcpdf.php');

function generarReportePDF($licencias_data, $licencia_por_cargo, $fecha_inicio, $fecha_fin, $grafico_img = null)
{
    // Limpiar el búfer de salida para evitar el error de TCPDF
    if (ob_get_length()) {
        ob_clean();
    }

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configuración del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Casa Borras');
    $pdf->SetTitle('Reporte Estadístico de Licencias');

    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 10);

    // Agregar una página
    $pdf->AddPage('P', 'LEGAL');
    $pdf->Image('assets/img/LOGO2.jpg', 95, 0, 0, 0);

    // Fuente
    $pdf->SetFont('helvetica', '', 12);

    // Título
    $pdf->Ln(5);
    $pdf->Write(0, "Reporte estadístico de licencias por tipo, estado y cargo.", '', 0, 'L', true, 0, false, false, 0);
$pdf->Ln(5); // Salto de línea de 5 mm
$pdf->Write(0, "(Período: $fecha_inicio al $fecha_fin)", '', 0, 'L', true, 0, false, false, 0);
    $pdf->Ln(5);

    // Crear tabla HTML
    $html = '<h3>Licencias por Cargo</h3>
          <table border="1" cellpadding="4">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th><strong>Cargo</strong></th>
                    <th><strong>Tipo de Licencia</strong></th>
                    <th><strong>Estado</strong></th>
                    <th><strong>Total Licencias</strong></th>
                    <th><strong>Total Días</strong></th>
                </tr>
            </thead>
            <tbody>';

    foreach ($licencia_por_cargo as $row) {
        $html .= '<tr>
                <td>' . htmlspecialchars($row['cargo']) . '</td>
                <td>' . htmlspecialchars($row['tipo_licencia']) . '</td>
                <td>' . htmlspecialchars($row['estado']) . '</td>
                <td align="center">' . $row['total_licencias'] . '</td>
                <td align="center">' . $row['total_dias'] . '</td>
              </tr>';
    }

    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(10); // 🛑 Espacio entre la tabla y el gráfico

    // 🖼️ Insertar gráfico si se envió
    if (isset($_POST['grafico_img'])) {
        $imgData = $_POST['grafico_img'];
        $pdf->Image('@' . base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imgData)), '', '', 180, 90, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
    }

    // Salida del PDF
    $pdf->Output('Reporte_Estadistico_Licencias.pdf', 'I');
    exit; // Asegura que no se envíe contenido adicional después
}
