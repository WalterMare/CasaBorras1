<?php
// Incluir las librerías necesarias
require_once('conexiondb.php');
require_once('select_UltimosEmpleados.php');
require_once('TCPDF-main/tcpdf.php'); // Asegúrate de tener TCPDF configurado correctamente

// Recibir los datos del filtro enviados por AJAX
$grupo = isset($_POST['grupo']) ? $_POST['grupo'] : 1;  // Por defecto, 1 año
$grupo1 = isset($_POST['grupo1']) ? $_POST['grupo1'] : 2;  // Filtro de estado (vacío para ambos)

// Conexión a la base de datos
$conexion = ConexionBD();

// Obtener los datos según los filtros seleccionados
if ($grupo1 !== 2) {
    $datos = Listar_ultimosEmpleados($conexion, $grupo, $grupo1); // Si hay estado (activo/inactivo)
} else {
    $datos = Listar_ultimosEmpleados2($conexion, $grupo); // Solo por tiempo
}

// Verifica que se obtienen datos
if (empty($datos)) {
    echo "No se encontraron datos para el reporte.";
    exit;
}

// Generación del PDF con TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración del PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Casa Borras S.A');
$pdf->SetTitle('Reporte de Últimos Empleados');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Agregar una página
$pdf->AddPage();

// Configurar fuente
$pdf->SetFont('helvetica', '', 11);

// Crear el contenido HTML
$html = '
    <style>
        h1 { font-family: Arial, Helvetica, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
    <img src="assets/img/LOGO2.jpg" alt="logo">
    <h1>Reporte de Últimos Empleados Registrados</h1>
    <p><strong>Período:</strong> ' . ($grupo == 1 ? '1 Año' : ($grupo == 3 ? '3 Años' : '5 Años')) . '</p>
    <p><strong>Estado:</strong> ' . ($grupo1 == 2 ? 'Ambos' : ($grupo1 == 1 ? 'Activo' : 'Inactivo')) . '</p>
    <table>
        <tr>
            <th>#</th>
            <th>Empleado</th>
            <th>Fecha de Inicio</th>
            <th>Estado</th>
            <th>Cargo</th>
        </tr>';

foreach ($datos as $i => $row) {
    $html .= '
        <tr>
            <td>' . ($i + 1) . '</td>
            <td>' . $row['NOMBRE'] . ' ' . $row['APELLIDO'] . '</td>
            <td>' . $row['FECHA_INICIO'] . '</td>
            <td>' . ($row['ESTADO'] == 1 ? 'Activo' : 'Inactivo') . '</td>
            <td>' . $row['CARGO'] . '</td>
        </tr>';
}

$html .= '</table>';

// Escribir el contenido HTML en el PDF
$pdf->writeHTML($html, true, false, false, false, 'C');

// Salvar el PDF en la respuesta
$pdf->lastPage();
ob_end_clean();
$pdf->Output('Reporte_Empleados.pdf', 'I'); // Esto lo envía al navegador para su visualización
?>
