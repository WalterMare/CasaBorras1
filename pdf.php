<?php
// Incluir las librerías necesarias
require_once('conexiondb.php');
require_once('select_UltimosEmpleados.php');
require_once('TCPDF-main/tcpdf.php'); // Asegúrate de tener TCPDF configurado correctamente

// Recibir los datos del filtro enviados por AJAX
$grupo = isset($_POST['grupo']) ? $_POST['grupo'] : 1;  // Por defecto, 1 año
$grupo1 = isset($_POST['grupo1']) ? (int)$_POST['grupo1'] : 2;  // Filtro de estado (vacío para ambos)

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

// Determinar el texto del estado general según el filtro aplicado
$estadoTexto = 'Ambos';
if ($grupo1 === 1) {
    $estadoTexto = 'Activo';
} elseif ($grupo1 === 0 || 'null') {
    $estadoTexto = 'Inactivo';
} elseif ($grupo1 === 3 && 0) { // Inactivos por baja
    $estadoTexto = 'Inactivo por baja';
}

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
    <p><strong>Estado:</strong> ' . $estadoTexto . '</p>
    <table>
        <tr>
            <th><strong>#</strong></th>
            <th><strong>Empleado</strong></th>
            <th><strong>Fecha de Inicio</strong></th>
            <th><strong>Fecha de Baja</strong></th>
            <th><strong>Estado</strong></th>
            <th><strong>Cargo</strong></th>
        </tr>';

// Generar filas de empleados
foreach ($datos as $i => $row) {
    // Consideramos que no hay baja si FECHA_BAJA está vacío o es "N/A"
    $noBaja = (empty($row['FECHA_BAJA']) || strtoupper(trim($row['FECHA_BAJA'])) == 'N/A');
    
    $estado = '';

    // Filtrado según $grupo1:
    if ($grupo1 == 1) { 
        // Filtro Activo: mostrar solo empleados activos sin baja
        if ($row['ESTADO'] == 1 && $noBaja) {
            $estado = 'Activo';
        } else {
            continue; // Salta empleados que no cumplen
        }
    } elseif ($grupo1 == 0) {
        // Filtro Inactivo: mostrar solo empleados inactivos (ESTADO == 0)
        // Si NO tienen baja, se muestran como "Inactivo", si tienen baja, como "Inactivo por baja"
        if ($row['ESTADO'] == 0) {
            $estado = $noBaja ? 'Inactivo' : 'Inactivo por baja';
        } else {
            continue;
        }
    } elseif ($grupo1 == 3) {
        // Filtro Inactivo por baja: mostrar solo empleados con baja
        if (!$noBaja) {
            $estado = 'Inactivo por baja';
        } else {
            continue;
        }
    } else { // Filtro Ambos (grupo1 == 2)
        // Aquí mostramos todos: usamos el ESTADO para determinar
        if ($row['ESTADO'] == 1 && $noBaja) {
            $estado = 'Activo';
        } elseif ($row['ESTADO'] == 0 && $noBaja) {
            $estado = 'Inactivo';
        } elseif (!$noBaja) {
            $estado = 'Inactivo por baja';
        }
    }
    
    // Mostrar la fecha de baja solo si es válida, de lo contrario '-'
    $fechaBaja = (!$noBaja) ? $row['FECHA_BAJA'] : '-';

    $html .= '
        <tr>
            <td>' . ($i + 1) . '</td>
            <td>' . htmlspecialchars($row['NOMBRE'] . ' ' . $row['APELLIDO']) . '</td>
            <td>' . htmlspecialchars($row['FECHA_INICIO']) . '</td>
            <td>' . $fechaBaja . '</td>
            <td>' . $estado . '</td>
            <td>' . htmlspecialchars($row['CARGO']) . '</td>
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
