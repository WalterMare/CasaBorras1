<?php

session_start();
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// Filtros de fechas
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin    = $_GET['fechaFin'] ?? '';

if (empty($fechaInicio) || empty($fechaFin)) {
    exit('Debe indicar fecha de inicio y fin.');
}

// Consultar datos desde DB
$empleadosAltas = $conexion->query("
    SELECT e.nombre, e.apellido, c.descripcion AS cargo, e.fecha_inicio
    FROM empleado e
    LEFT JOIN cargo c ON e.idcargo = c.idcargo
    WHERE e.fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin'
");

$empleadosBajas = $conexion->query("
    SELECT e.nombre, e.apellido, c.descripcion AS cargo, e.fecha_baja, e.motivo_baja
    FROM empleado e
    LEFT JOIN cargo c ON e.idcargo = c.idcargo
    WHERE e.fecha_baja BETWEEN '$fechaInicio' AND '$fechaFin'
");

// Obtener indicadores generales
require_once 'Consulta_RotacionPersonal.php';
$resultado = obtenerRotacionPersonal($conexion, $fechaInicio, $fechaFin);

// --- PhpSpreadsheet ---
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Crear hoja
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rotación de Personal');

// Estilo cabecera
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// --- Resumen ---
$sheet->fromArray([
    ['Reporte Completo de Rotación de Personal'],
    ['Fecha Inicio:', $fechaInicio],
    ['Fecha Fin:', $fechaFin],
    ['Altas', $resultado['Altas']],
    ['Bajas', $resultado['Bajas']],
    ['Plantilla Inicial', $resultado['PlantillaInicial']],
    ['Plantilla Final', $resultado['PlantillaFinal']],
    ['Plantilla Promedio', number_format($resultado['PlantillaPromedio'], 2)],
    ['Rotación (%)', number_format($resultado['RotacionPorcentaje'], 2) . '%']
], null, 'A1');

// Aplicar estilo solo a A1
$sheet->getStyle('A1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 16, // tamaño de letra más grande
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ]
]);

// Opcional: unir varias columnas si querés que se vea centrado
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

// --- Detalle Altas ---
$sheet->fromArray(['Nombre', 'Apellido', 'Cargo', 'Fecha de Alta'], null, 'A11');
$sheet->getStyle('A11:D11')->applyFromArray($headerStyle);

$row = 12;
while ($fila = $empleadosAltas->fetch_assoc()) {
    $sheet->fromArray([
        $fila['nombre'],
        $fila['apellido'],
        $fila['cargo'],
        $fila['fecha_inicio']
    ], null, 'A' . $row);
    $row++;
}

// --- Detalle Bajas ---
$startBajas = $row + 2;
$sheet->fromArray(['Nombre', 'Apellido', 'Cargo', 'Fecha de Baja', 'Motivo'], null, 'A' . $startBajas);
$sheet->getStyle('A' . $startBajas . ':E' . $startBajas)->applyFromArray($headerStyle);

$rowBajas = $startBajas + 1;
while ($fila = $empleadosBajas->fetch_assoc()) {
    $sheet->fromArray([
        $fila['nombre'],
        $fila['apellido'],
        $fila['cargo'],
        $fila['fecha_baja'],
        $fila['motivo_baja']
    ], null, 'A' . $rowBajas);
    $rowBajas++;
}

// --- Ajuste columnas y wrap text ---
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->getStyle('A1:E1000')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);

// --- Graficos ---
// 1) Altas vs Bajas
$sheet->setCellValue('A4', 'Altas');
$sheet->setCellValue('A5', 'Bajas');
$sheet->setCellValue('B4', $resultado['Altas']);
$sheet->setCellValue('B5', $resultado['Bajas']);

$categories = [new DataSeriesValues('String', "'Rotación de Personal'!\$A\$4:\$A\$5")];
$values = [new DataSeriesValues('Number', "'Rotación de Personal'!\$B\$4:\$B\$5")];
$series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, range(0, count($values) - 1), [], $categories, $values);
$plotArea = new PlotArea(null, [$series]);
$chart = new Chart('Altas vs Bajas', new Title('Altas vs Bajas'), new Legend(Legend::POSITION_RIGHT, null, false), $plotArea, true, 0, null, null);
$chart->setTopLeftPosition('G2');
$chart->setBottomRightPosition('N15');
$sheet->addChart($chart);

// 2) Plantilla Inicial vs Final
$sheet->setCellValue('A6', 'Inicial');
$sheet->setCellValue('A7', 'Final');
$sheet->setCellValue('B6', $resultado['PlantillaInicial']);
$sheet->setCellValue('B7', $resultado['PlantillaFinal']);

$categories2 = [new DataSeriesValues('String', "'Rotación de Personal'!\$A\$6:\$A\$7")];
$values2 = [new DataSeriesValues('Number', "'Rotación de Personal'!\$B\$6:\$B\$7")];
$series2 = new DataSeries(DataSeries::TYPE_LINECHART, null, range(0, count($values2) - 1), [], $categories2, $values2);
$plotArea2 = new PlotArea(null, [$series2]);
$chart2 = new Chart('Plantilla Inicial vs Final', new Title('Plantilla Inicial vs Final'), new Legend(Legend::POSITION_RIGHT, null, false), $plotArea2, true, 0, null, null);
$chart2->setTopLeftPosition('G17');
$chart2->setBottomRightPosition('N30');
$sheet->addChart($chart2);

// 3) Rotación (%)
$sheet->setCellValue('A8', 'Rotación');
$sheet->setCellValue('A9', 'Plantilla Estable');
$sheet->setCellValue('B8', $resultado['RotacionPorcentaje']);
$sheet->setCellValue('B9', 100 - $resultado['RotacionPorcentaje']);

$categories3 = [new DataSeriesValues('String', "'Rotación de Personal'!\$A\$8:\$A\$9")];
$values3 = [new DataSeriesValues('Number', "'Rotación de Personal'!\$B\$8:\$B\$9")];

$series3 = new DataSeries(DataSeries::TYPE_PIECHART, null, range(0, count($values3) - 1), [], $categories3, $values3);
$plotArea3 = new PlotArea(null, [$series3]);
$chart3 = new Chart('Rotación (%)', new Title('Rotación (%)'), new Legend(Legend::POSITION_RIGHT, null, false), $plotArea3, true, 0, null, null);
$chart3->setTopLeftPosition('O2');
$chart3->setBottomRightPosition('U15');
$sheet->addChart($chart3);

// --- Exportar ---
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte_Rotacion_Personal.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;
