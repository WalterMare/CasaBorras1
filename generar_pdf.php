<?php
require_once('TCPDF-main/tcpdf.php');

// Función para generar el reporte en PDF
function generarReportePDF($licencias_data)
{
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    // Agregar logo
    $logoPath = 'assets/img/LOGO2.jpg'; // Cambia esto por la ruta de tu archivo de logo
    $pdf->Image($logoPath, 90, 0, 20, 20, 'JPG'); // (ruta, x, y, ancho, alto, tipo)
    // Título del reporte
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 30, 'Reporte Estadístico de Licencias', 0, 1, 'C');

    // Preparar datos para la tabla y el gráfico
    $licencia_tipos = prepararDatosReporte($licencias_data);

    // Generar gráfico de barras
    $grafico = generarGraficoBarras($licencia_tipos);
    if ($grafico) {
        $pdf->Image($grafico, 10, 40, 180, 80, 'PNG');
    }
    $pdf->Ln(100);

    // Mostrar tabla de estadísticas
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 10, 'Tipo de Licencia', 1);
    $pdf->Cell(40, 10, 'Estado', 1);
    $pdf->Cell(50, 10, 'Total Licencias', 1);
    $pdf->Cell(40, 10, 'Total Días', 1);
    $pdf->Ln();

    $pdf->SetFont('helvetica', '', 10);
    foreach ($licencias_data as $data) {
        $pdf->Cell(50, 10, $data['tipo_licencia'], 1);
        $pdf->Cell(40, 10, $data['estado'], 1);
        $pdf->Cell(50, 10, $data['total_licencias'], 1);
        $pdf->Cell(40, 10, $data['total_dias'], 1);
        $pdf->Ln();
    }

    // Totales generales
    $totalLicencias = array_sum(array_column($licencias_data, 'total_licencias'));
    $totalDias = array_sum(array_column($licencias_data, 'total_dias'));
    $pdf->Cell(90, 10, 'Totales', 1, 0, 'C');
    $pdf->Cell(50, 10, $totalLicencias, 1, 0, 'C');
    $pdf->Cell(40, 10, $totalDias, 1, 1, 'C');

    // Salida del PDF
    $pdf->Output('reporte_estadistico_licencias.pdf', 'D');
}

// Preparar datos para gráficos
function prepararDatosReporte($licencias_data)
{
    $licencia_tipos = [];
    foreach ($licencias_data as $data) {
        $licencia_tipos[$data['tipo_licencia']][$data['estado']] = $data['total_licencias'];
    }
    return $licencia_tipos;
}

// Generar gráfico de barras
function generarGraficoBarras($licencia_tipos)
{
    $ancho = 500;
    $alto = 400;
    $imagen = imagecreatetruecolor($ancho, $alto);

    // Colores
    $blanco = imagecolorallocate($imagen, 255, 255, 255);
    $negro = imagecolorallocate($imagen, 0, 0, 0);
    $azul = imagecolorallocate($imagen, 100, 150, 255);
    $gris = imagecolorallocate($imagen, 200, 200, 200);

    // Fondo blancofunction genfunction generarGraficoBarras($licencia_tipos) {
    $ancho = 800; // Ajustamos el ancho para acomodar más datos
    $alto = 400;
    $imagen = imagecreatetruecolor($ancho, $alto);

    // Colores
    $blanco = imagecolorallocate($imagen, 255, 255, 255);
    $negro = imagecolorallocate($imagen, 0, 0, 0);
    $colorAprobado = imagecolorallocate($imagen, 100, 150, 255); // Azul para "Aprobado"
    $colorFinalizado = imagecolorallocate($imagen, 255, 87, 51); // Morado para "Finalizado"

    // Fondo blanco
    imagefilledrectangle($imagen, 0, 0, $ancho, $alto, $blanco);

    // Fuente
    $fuente = 'C:/Windows/Fonts/arial.ttf'; // Cambia según tu sistema operativo

    // Ejes
    imageline($imagen, 50, 30, 50, $alto - 50, $negro); // Eje Y
    imageline($imagen, 30, $alto - 50, $ancho - 50, $alto - 50, $negro); // Eje X

    // Título del gráfico
    imagettftext($imagen, 14, 0, ($ancho / 2) - 100, 20, $negro, $fuente, 'Licencias por Tipo y Estado');

    // Cálculo de barras
    $max_value = max(array_map('max', $licencia_tipos));
    $x = 70; // Inicio de las barras en el eje X
    $anchoBarra = 20;
    $espaciado = 40; // Espaciado entre grupos de barras
    $margenSuperior = 80;

    foreach ($licencia_tipos as $tipo => $estados) {
        $grupoX = $x; // Posición inicial del grupo

        foreach ($estados as $estado => $total) {
            // Altura de la barra según el total y el máximo
            $bar_height = ($total / $max_value) * ($alto - $margenSuperior - 70);

            // Seleccionar color según el estado
            $colorBarra = ($estado === 'Aprobado') ? $colorAprobado : $colorFinalizado;

            // Dibujar barra
            imagefilledrectangle($imagen, $grupoX, $alto - 50, $grupoX + $anchoBarra, $alto - 50 - $bar_height, $colorBarra);

            // Etiqueta del total sobre la barra
            imagettftext($imagen, 8, 0, $grupoX, $alto - 60 - $bar_height, $negro, $fuente, $total);

            // Avanzar al siguiente estado dentro del grupo
            $grupoX += $anchoBarra + 10; // Espacio entre barras del mismo grupo
        }

        // Etiqueta del tipo de licencia en el eje X
        imagettftext($imagen, 10, 0, $x, $alto - 30, $negro, $fuente, $tipo);

        // Avanzar al siguiente grupo de barras
        $x += $espaciado + count($estados) * ($anchoBarra + 10); // Espaciado ajustado según el número de estados
    }

    // Leyenda
    imagettftext($imagen, 10, 0, $ancho - 300, $alto - 320, $colorAprobado, $fuente, 'Aprobada (Azul)');
    imagettftext($imagen, 10, 0, $ancho - 300, $alto - 340, $colorFinalizado, $fuente, 'Finalizada (Rojo)');

    // Guardar y devolver
    $ruta = 'grafico_barras.png';
    imagepng($imagen, $ruta);
    imagedestroy($imagen);

    return $ruta;
}
