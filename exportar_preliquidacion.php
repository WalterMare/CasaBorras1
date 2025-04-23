<?php
require 'vendor/autoload.php'; // Asegúrate de incluir la librería PhpSpreadsheet

require_once 'conexiondb.php';

$conexion = ConexionBD();

// Si no se ha pasado un idPreliquidacion
if (!isset($_GET['idPreliquidacion'])) {
    die("Error: ID de preliquidación no especificado.");
}

$idPreliquidacion = intval($_GET['idPreliquidacion']);

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
    e.nombre, e.apellido, e.dni, 
    c.descripcion AS cargo,  
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
    vacaciones v ON v.idempleado = e.idempleado AND v.año = YEAR(CURDATE())
WHERE 
    dp.idPreliquidacion = ?
GROUP BY 
    e.idempleado, c.descripcion, dp.idHorasExtras, dp.idLicencia, dp.idAnticipo, 
    dp.idObraSocial, dp.idFamiliar, dp.idSancion, dp.idEmbargo, dp.idViatico, 
    dp.diasTrabajados, dp.tiposSanciones, dp.tiposLicencias, dp.vacacionesTomadas";

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

// Definir las cabeceras para la tabla de detalles
$sheet->setCellValue('A7', 'Nombre');
$sheet->setCellValue('B7', 'Apellido');
$sheet->setCellValue('C7', 'DNI');
$sheet->setCellValue('D7', 'Cargo');
$sheet->setCellValue('E7', 'Horas Extras');
$sheet->setCellValue('F7', 'Licencias');
$sheet->setCellValue('G7', 'Anticipos');
$sheet->setCellValue('H7', 'Obra Social');
$sheet->setCellValue('I7', 'Familiares');
$sheet->setCellValue('J7', 'Sanciones');
$sheet->setCellValue('K7', 'Embargos');
$sheet->setCellValue('L7', 'Viáticos');
$sheet->setCellValue('M7', 'Días Trabajados');
$sheet->setCellValue('N7', 'Tipo Sanción');
$sheet->setCellValue('O7', 'Tipo Licencia');
$sheet->setCellValue('P7', 'Vacaciones Tomadas');
$sheet->setCellValue('Q7', 'Vacaciones Restantes');

// Agregar los detalles de los empleados a las filas siguientes
$row = 8;
while ($detalle = mysqli_fetch_assoc($resultDetalles)) {
    $sheet->setCellValue('A' . $row, $detalle['nombre']);
    $sheet->setCellValue('B' . $row, $detalle['apellido']);
    $sheet->setCellValue('C' . $row, $detalle['dni']);
    $sheet->setCellValue('D' . $row, $detalle['cargo']);
    $sheet->setCellValue('E' . $row, $detalle['idHorasExtras'] > 0 ? $detalle['idHorasExtras'] . ' horas' : 'No Registra');
    $sheet->setCellValue('F' . $row, $detalle['idLicencia'] > 0 ? $detalle['idLicencia'] . ' días' : 'No Registra');
    $sheet->setCellValue('G' . $row, $detalle['idAnticipo']);
    $sheet->setCellValue('H' . $row, $detalle['idObraSocial']);
    $sheet->setCellValue('I' . $row, $detalle['idFamiliar']);
    $sheet->setCellValue('J' . $row, $detalle['idSancion']);
    $sheet->setCellValue('K' . $row, $detalle['idEmbargo']);
    $sheet->setCellValue('L' . $row, $detalle['idViatico']);
    $sheet->setCellValue('M' . $row, $detalle['diasTrabajados']);
    $sheet->setCellValue('N' . $row, $detalle['tipoSancion']);
    $sheet->setCellValue('O' . $row, $detalle['tipoLicencia']);
    $sheet->setCellValue('P' . $row, $detalle['vacacionesTomadas']);
    $sheet->setCellValue('Q' . $row, $detalle['vacacionesRestantes']);
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
?>
