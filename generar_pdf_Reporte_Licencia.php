<?php
ob_start(); // Inicia el búfer de salida

// Incluir la librería TCPDF
require_once('TCPDF-main/tcpdf.php');  // Asegúrate de cambiar la ruta correctamente

// Verificar si la sesión está activa
session_start();
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

// Conectar a la base de datos
require_once 'conexiondb.php';
$conexion = ConexionBD();

// Obtener el ID del empleado desde el parámetro GET
$empleado_id = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;

require_once 'select_empleado.php';
$empleado = Listar_empleadoId($conexion, $empleado_id);

// Obtener los datos del reporte
require_once 'Select_reportes.php';
$tipo_reporte = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : '';

// Obtener el ID de la licencia desde el parámetro GET
$idLicencia = isset($_GET['id_licencia']) ? (int)$_GET['id_licencia'] : 0;

// Consulta SQL para obtener las licencias y sus detalles
if ($tipo_reporte == 1) {
    // Obtener las licencias del empleado
$listadoLicencias = Listar_Reporte_Empleado3($conexion, $empleado_id, 1);
} else if ($tipo_reporte == 6) {
    $listadoEmbargo = Listar_Reporte_Empleado_Embargo($conexion, $empleado_id);
}




ob_end_clean(); // Limpia cualquier salida antes de la creación del PDF
// Crear el objeto TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['Usuario_Nombre']);
$pdf->SetTitle('Reporte del Empleado');
$pdf->SetSubject('Reporte de Licencias');

// Establecer márgenes y auto-salto de página
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(true, 10);

// Agregar una página
$pdf->AddPage('P', 'LEGAL');
$pdf->Image('assets/img/LOGO2.jpg', 95, 0, 0, 0);
// Establecer el título del reporte
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'Reporte', 0, 1, 'C');


// Agregar la información del empleado
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Ln(10);
$pdf->Cell(70, 10, 'Empleado: ' . $empleado['NOMBRE'] . " " . $empleado['APELLIDO'], 0, 1);
$pdf->Cell(40, 10, 'DNI: ' . $empleado['DNI'], 0, 1);
$pdf->Cell(40, 10, 'Sexo: ' . $empleado['SEXO'], 0, 1);
$pdf->Cell(40, 10, 'Estado: ' . $empleado['ESTADO'], 0, 1);
$pdf->Cell(70, 10, 'Fecha de Inicio: ' . $empleado['FECHAINICIO'], 0, 1);
$pdf->Cell(40, 10, 'Cargo: ' . $empleado['CARGO'], 0, 1);
$pdf->Cell(70, 10, 'Ciudad: ' . $empleado['CIUDAD'], 0, 1);
$pdf->Cell(40, 10, 'Provincia: ' . $empleado['PROVINCIA'], 0, 1);

// Agregar los datos de licencias si el tipo de reporte es '1'
if ($tipo_reporte == '1') {
   // Verificar si hay licencias
if (!empty($listadoLicencias)) {
    foreach ($listadoLicencias as $licencia) {
        // Mostrar datos básicos de la licencia
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Ln(10);
        $pdf->Cell(70, 10, 'ID Licencia: ' . $licencia['ID'], 0, 1);
        $pdf->Cell(70, 10, 'Tipo de Licencia: ' . $licencia['TIPO'], 0, 1);
        $pdf->Cell(70, 10, 'Fecha Inicio: ' . $licencia['FECHAINICIO'], 0, 1);
        $pdf->Cell(70, 10, 'Fecha Fin: ' . $licencia['FECHAFIN'], 0, 1);
        $pdf->Cell(70, 10, 'Cantidad de Días: ' . $licencia['CANTIDADDIAS'], 0, 1);
        $pdf->Cell(70, 10, 'Estado: ' . $licencia['ESTADO'], 0, 1);

        // Obtener los detalles de la licencia
        $listadoDetalles = Listar_Reporte_Empleado3_Detalle($conexion, $licencia['ID']);

        if (!empty($listadoDetalles) && !isset($listadoDetalles['mensaje'])) {
            // Cabecera de los detalles
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Detalles de la Licencia', 0, 1);

            // Ajustar columnas
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(40, 10, 'Descripción', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Fecha Creación', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Estado', 1, 1, 'C');

            // Rellenar los detalles
            foreach ($listadoDetalles as $detalle) {
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(40, 10, $detalle['DESCRIPCION'], 1, 0);
                $pdf->Cell(40, 10, $detalle['FECHACREACION'], 1, 0);
                $pdf->Cell(30, 10, $detalle['ESTADO_LICENCIA'], 1, 1);
                // Agregar el enlace de documentación
                if (!empty($detalle['DOCUMENTACION'])) {
                    $pdf->SetFont('helvetica', 'I', 10);
                    $pdf->writeHTMLCell(0, 10, '', '', '<a href="data:application/octet-stream;base64,' . base64_encode($detalle['DOCUMENTACION']) . '">Descargar Documentación</a>', 0, 1, false, true, 'L');
                }
            }
        } else {
            // Mostrar mensaje si no hay detalles
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'No hay detalles disponibles para esta licencia.', 0, 1, 'C');
        }
    }
} else {
    // Mostrar mensaje si no hay licencias
    $pdf->Ln(20);
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 10, 'El empleado seleccionado no tiene licencias.', 0, 1, 'C');
}
}
if ($tipo_reporte == '6') {
    // Ajuste de la tabla para que ocupe el ancho completo de la página
    $anchoTotal = $pdf->getPageWidth();
    $anchoColumna = $anchoTotal / 8; // Ajustamos el número de columnas

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Embargos', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell($anchoColumna, 10, '#', 1, 0, 'C');
    $pdf->Cell($anchoColumna, 10, 'Fecha', 1, 0, 'C');
    $pdf->Cell($anchoColumna, 10, 'Monto', 1, 0, 'C');
    $pdf->Cell(150, 10, 'Descripción', 1, 1, 'C');

    if (!empty($listadoEmbargo)) {
        // Agregar las licencias al PDF
        foreach ($listadoEmbargo as $index => $embargo) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell($anchoColumna, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell($anchoColumna, 10, $embargo['FECHA'], 1, 0, 'C');
            $pdf->Cell($anchoColumna, 10, $embargo['MONTO'], 1, 0, 'C');
            $pdf->Cell(150, 10, $embargo['DESCRIPCION'], 1, 0, 'C');
        }
    } else {
        // Si no hay licencias, muestra un mensaje indicando que no hay licencias
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene Embargos.', 0, 1, 'C');
    }
}



// Salida del PDF
$pdf->Output('reporte_empleado_' . $empleado_id . '.pdf', 'D');
