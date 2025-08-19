<?php
require 'vendor/autoload.php'; // Asegúrate de incluir la librería PhpSpreadsheet

require_once 'conexiondb.php';

$conexion = ConexionBD();

// Si no se ha pasado un idPreliquidacion
if (!isset($_GET['id'])) {
    die("Error: ID de preliquidación no especificado.");
}

$idPreliquidacion = intval($_GET['id']);

// Consulta para obtener los datos de la preliquidación
$consultaPreliquidacion = "SELECT 
    p.idpreliquidacion, p.fecha, p.periodo, ep.descripcionPreliquidacion AS estado
FROM 
    preliquidacion p
JOIN 
    estadopreliquidacion ep ON p.idEstadoPre = ep.idestadoPreliquidacion
WHERE 
    p.idpreliquidacion = ?";

$stmt = mysqli_prepare($conexion, $consultaPreliquidacion);
mysqli_stmt_bind_param($stmt, "i", $idPreliquidacion);
mysqli_stmt_execute($stmt);
$resultPreliquidacion = mysqli_stmt_get_result($stmt);
$preliquidacion = mysqli_fetch_assoc($resultPreliquidacion);

// Consulta para obtener los detalles de empleados en la preliquidación
$consultaDetalles = "SELECT 
    e.nombre, e.apellido, e.dni, e.fecha_inicio,
    c.descripcion AS cargo,  
    c.sueldo_basico,
    dp.idHorasExtras, dp.idLicencia, dp.idAnticipo, 
    dp.idObraSocial, dp.idFamiliar, dp.idSancion, 
    dp.idEmbargo, dp.idViatico,
    dp.diasTrabajados,
    dp.tiposSanciones AS tipoSancion, 
    dp.tiposLicencias AS tipoLicencia,
    dp.vacacionesTomadas AS vacacionesTomadas,
    COALESCE(MAX(v.vacaciones_restantes), 0) AS vacacionesRestantes
FROM 
    detallepreliquidacion dp
INNER JOIN 
    empleado e ON dp.idEmpleado = e.idempleado
LEFT JOIN
    cargo c ON e.idCargo = c.idcargo
LEFT JOIN
    tipolicencia tl ON dp.tiposLicencias = tl.idtipoLicencia
LEFT JOIN
    vacaciones v ON v.idempleado = e.idempleado AND v.anio = YEAR(CURDATE())
WHERE 
    dp.idPreliquidacion = ?
GROUP BY 
    e.idempleado, c.descripcion, c.sueldo_basico,
    dp.idHorasExtras, dp.idLicencia, dp.idAnticipo, 
    dp.idObraSocial, dp.idFamiliar, dp.idSancion, dp.idEmbargo, dp.idViatico, 
    dp.diasTrabajados, dp.tiposSanciones, dp.tiposLicencias, dp.vacacionesTomadas";

// Ejecutar la consulta igual que antes
$stmtDetalles = mysqli_prepare($conexion, $consultaDetalles);
mysqli_stmt_bind_param($stmtDetalles, "i", $idPreliquidacion);
mysqli_stmt_execute($stmtDetalles);
$resultDetalles = mysqli_stmt_get_result($stmtDetalles);

// Crear nuevo archivo de Excel
$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Setear título de la hoja
$sheet->setCellValue('A1', 'Preliquidación');
$sheet->setCellValue('A2', 'ID Preliquidación: ' . $preliquidacion['idpreliquidacion']);
$sheet->setCellValue('A3', 'Fecha: ' . $preliquidacion['fecha']);
$sheet->setCellValue('A4', 'Periodo: ' . $preliquidacion['periodo']);
$sheet->setCellValue('A5', 'Estado: ' . $preliquidacion['estado']);
$sheet->setCellValue('A6', ' ');  // Deja espacio

// Cabeceras Excel: agregamos Fecha Inicio, Sueldo Básico, Antigüedad
$sheet->setCellValue('A7', 'Nombre');
$sheet->setCellValue('B7', 'Apellido');
$sheet->setCellValue('C7', 'DNI');
$sheet->setCellValue('D7', 'Cargo');
$sheet->setCellValue('E7', 'Sueldo Básico');
$sheet->setCellValue('F7', 'Fecha Inicio');
$sheet->setCellValue('G7', 'Antigüedad (años)');
$sheet->setCellValue('H7', 'Horas Extras');
$sheet->setCellValue('I7', 'Licencias');
$sheet->setCellValue('J7', 'Anticipos');
$sheet->setCellValue('K7', 'Obra Social');
$sheet->setCellValue('L7', 'Familiares');
$sheet->setCellValue('M7', 'Sanciones');
$sheet->setCellValue('N7', 'Embargos');
$sheet->setCellValue('O7', 'Viáticos');
$sheet->setCellValue('P7', 'Días Trabajados');
$sheet->setCellValue('Q7', 'Tipo Sanción');
$sheet->setCellValue('R7', 'Tipo Licencia');
$sheet->setCellValue('S7', 'Vacaciones Tomadas');
$sheet->setCellValue('T7', 'Vacaciones Restantes');
// --- Estilos para cabeceras ---
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$columnas = range('A', 'T'); // De A a T
foreach ($columnas as $col) {
    // Negrita
    $sheet->getStyle($col.'7')->getFont()->setBold(true);
    // Centrar el texto
    $sheet->getStyle($col.'7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    // Ajustar ancho automático
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// --- Agregar autofiltro ---
$sheet->setAutoFilter('A7:T7');

// Agregar los detalles de los empleados
$row = 8;
while ($detalle = mysqli_fetch_assoc($resultDetalles)) {
    $fechaInicio = $detalle['fecha_inicio'];
    $antiguedad = date_diff(date_create($fechaInicio), date_create())->y; // años

    $sheet->setCellValue('A' . $row, $detalle['nombre']);
    $sheet->setCellValue('B' . $row, $detalle['apellido']);
    $sheet->setCellValue('C' . $row, $detalle['dni']);
    $sheet->setCellValue('D' . $row, $detalle['cargo']);
    $sheet->setCellValue('E' . $row, number_format($detalle['sueldo_basico'], 2, ',', '.'));
    $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($fechaInicio)));
    $sheet->setCellValue('G' . $row, $antiguedad);
    $sheet->setCellValue('H' . $row, $detalle['idHorasExtras'] > 0 ? $detalle['idHorasExtras'] . ' horas' : 'No Registra');
    $sheet->setCellValue('I' . $row, $detalle['idLicencia'] > 0 ? $detalle['idLicencia'] . ' días' : 'No Registra');
    $sheet->setCellValue('J' . $row, $detalle['idAnticipo']);
    $sheet->setCellValue('K' . $row, $detalle['idObraSocial']);
    $sheet->setCellValue('L' . $row, $detalle['idFamiliar']);
    $sheet->setCellValue('M' . $row, $detalle['tipoSancion']);
    $sheet->setCellValue('N' . $row, $detalle['idEmbargo']);
    $sheet->setCellValue('O' . $row, $detalle['idViatico']);
    $sheet->setCellValue('P' . $row, $detalle['diasTrabajados']);
    $sheet->setCellValue('Q' . $row, $detalle['tipoSancion']);
    $sheet->setCellValue('R' . $row, $detalle['tipoLicencia']);
    $sheet->setCellValue('S' . $row, $detalle['vacacionesTomadas']);
    $sheet->setCellValue('T' . $row, $detalle['vacacionesRestantes']);
    $row++;
}
// Guardar archivo de Excel
$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$filename = "preliquidacion_" . $idPreliquidacion . ".xlsx";

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Escribir el archivo al navegador
$writer->save('php://output');
exit;
