<?php
require_once 'TCPDF-main/tcpdf.php'; // Asegúrate de que TCPDF está instalado y en la ruta correcta
require_once 'conexiondb.php';
require_once 'select_UltimosEmpleados.php';

session_start();

// Verifica si hay filtros almacenados en la sesión
$lapsoTiempo = isset($_SESSION['RadioSeleccionado']) ? $_SESSION['RadioSeleccionado'] : 'No especificado';
$estadoEmpleado = isset($_SESSION['RadioSeleccionado2']) ? $_SESSION['RadioSeleccionado2'] : 'No especificado';

// Mapea los valores de los filtros a descripciones
$lapsoTiempoTexto = [
    "1" => "1 año",
    "3" => "3 años",
    "5" => "5 años"
][$lapsoTiempo] ?? "No especificado";

$estadoEmpleadoTexto = [
    "1" => "Activo",
    "4" => "Inactivo",
    "2" => "Activos e Inactivos",
    "3" => "Inactivo por Baja"
][$estadoEmpleado] ?? "No especificado";

$MiConexion = ConexionBD();
$listadoEmpleados = Listar_ultimosEmpleados($MiConexion, $lapsoTiempo, $estadoEmpleado);

// Crea una nueva instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Recursos Humanos');
$pdf->SetTitle('Listado de Últimos Empleados Registrados');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->Image('assets/img/LOGO2.jpg', 95, 0, 0, 0);

// Título
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 30, 'Listado de Últimos Empleados Registrados', 0, 1, 'C');
$pdf->Ln(-5);

// Filtros utilizados
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, "Filtro aplicado - Tiempo: $lapsoTiempoTexto | Estado: $estadoEmpleadoTexto", 0, 1, 'C');
$pdf->Ln(5);

// Encabezado de la tabla
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(10, 10, '#', 1, 0, 'C');
$pdf->Cell(30, 10, 'Empleado', 1, 0, 'C');
$pdf->Cell(30, 10, 'Fecha Inicio', 1, 0, 'C');
$pdf->Cell(30, 10, 'Fecha Baja', 1, 0, 'C');
$pdf->Cell(25, 10, 'Estado', 1, 0, 'C');
$pdf->Cell(60, 10, 'Cargo', 1, 1, 'C');

// Contenido de la tabla
$pdf->SetFont('helvetica', '', 10);
foreach ($listadoEmpleados as $index => $empleado) {
    $pdf->Cell(10, 10, $index + 1, 1, 0, 'C');
    $pdf->Cell(30, 10, $empleado['NOMBRE'] . ' ' . $empleado['APELLIDO'], 1, 0, 'L');
    $pdf->Cell(30, 10, $empleado['FECHA_INICIO'], 1, 0, 'C');
    $pdf->Cell(30, 10, $empleado['FECHA_BAJA'], 1, 0, 'C');
    $estadoTexto = $empleado['ESTADO'] == 1 ? 'Activo' : 'Inactivo';
    $pdf->Cell(25, 10, $estadoTexto, 1, 0, 'C');
    $pdf->Cell(60, 10, $empleado['CARGO'], 1, 1, 'L');
}

// Salida del PDF
$pdf->Output('Listado_Ultimos_Empleados.pdf', 'I');
