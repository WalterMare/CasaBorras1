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
} else if ($tipo_reporte == 2) {
    $listadoSancion = Listar_Reporte_Empleado_Sancion($conexion, $empleado_id);
} else if ($tipo_reporte == 3) {
    $listadoExtras = Listar_Reporte_Empleado_HorasExtras($conexion, $empleado_id);
} else if ($tipo_reporte == 5) {
    $listadoViatico = Listar_Reporte_Viaticos_Empleado($conexion, $empleado_id);
} else if ($tipo_reporte == 4) {
    // Obtener el reporte de asistencias
    $reporte = Listar_Reporte_Asistencias_Empleado($conexion, $empleado_id);
    $listadoAsistencia = $reporte['asistencias'];
    $totalHoras = $reporte['total_horas']; // Total de horas trabajadas en el mes
} else if ($tipo_reporte == 7) {
    $listadoVacaciones = Listar_Reporte_Empleado_Vacaciones($conexion, $empleado_id);
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


// Información del Empleado - Encabezado con fondo gris
$pdf->Ln(10);
$pdf->SetFillColor(230, 230, 230); // Gris claro
$pdf->SetTextColor(0);             // Texto negro
$pdf->SetDrawColor(180, 180, 180); // Borde gris claro
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'Datos del Empleado', 1, 1, 'L', true);

// Configuración base
$pdf->SetFont('helvetica', '', 11);
$pdf->SetFillColor(245, 245, 245); // Etiqueta: gris más claro
$pdf->SetTextColor(0);
$h = 8; // Altura
$w_etiqueta = 40;
$w_valor = 53;

// FILA 1
$pdf->Cell($w_etiqueta, $h, 'Nombre:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['NOMBRE'] . ' ' . $empleado['APELLIDO'], 1, 0);
$pdf->Cell($w_etiqueta, $h, 'DNI:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['DNI'], 1, 1);

// FILA 2
$pdf->Cell($w_etiqueta, $h, 'Sexo:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['SEXO'], 1, 0);
$pdf->Cell($w_etiqueta, $h, 'Estado:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['ESTADO'], 1, 1);

// FILA 3
$pdf->Cell($w_etiqueta, $h, 'Fecha de Inicio:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['FECHAINICIO'], 1, 0);
$pdf->Cell($w_etiqueta, $h, 'Cargo:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['CARGO'], 1, 1);

// FILA 4
$pdf->Cell($w_etiqueta, $h, 'Ciudad:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['CIUDAD'], 1, 0);
$pdf->Cell($w_etiqueta, $h, 'Provincia:', 1, 0, 'L', true);
$pdf->Cell($w_valor, $h, $empleado['PROVINCIA'], 1, 1);

// Agregar los datos de licencias si el tipo de reporte es '1'
if ($tipo_reporte == '1') {
    // Verificar si hay licencias
    $contadorLicencias = 1;
    if (!empty($listadoLicencias)) {
        foreach ($listadoLicencias as $licencia) {

            // Espaciado entre licencias
            $pdf->Ln(5);

            // Tabla principal de la Licencia
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, 'Licencia ' . $contadorLicencias, 0, 1, 'L');

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(45, 8, 'Fecha Inicio', 1, 0, 'C');
            $pdf->Cell(45, 8, 'Fecha Fin', 1, 0, 'C');
            $pdf->Cell(30, 8, 'Días', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Tipo', 1, 0, 'C');
            $pdf->Cell(30, 8, 'Estado', 1, 1, 'C');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(45, 8, $licencia['FECHAINICIO'], 1, 0, 'C');
            $pdf->Cell(45, 8, $licencia['FECHAFIN'], 1, 0, 'C');
            $pdf->Cell(30, 8, $licencia['CANTIDADDIAS'], 1, 0, 'C');
            $pdf->Cell(40, 8, $licencia['TIPO'], 1, 0, 'C');
            $pdf->Cell(30, 8, $licencia['ESTADO'], 1, 1, 'C');
            $contadorLicencias++;
            // DETALLES
            $listadoDetalles = Listar_Reporte_Empleado3_Detalle($conexion, $licencia['ID']);

            if (!empty($listadoDetalles) && !isset($listadoDetalles['mensaje'])) {

                // Encabezado de detalles
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(70, 8, 'Descripción', 1, 0, 'C');
                $pdf->Cell(40, 8, 'Fecha de Creación', 1, 0, 'C');
                $pdf->Cell(80, 8, 'Usuario Otorga', 1, 1, 'C');

                // Filas de detalles
                $pdf->SetFont('helvetica', '', 10);
                foreach ($listadoDetalles as $detalle) {
                    $pdf->Cell(70, 8, $detalle['DESCRIPCION'], 1, 0, 'C');
                    $pdf->Cell(40, 8, $detalle['FECHACREACION'], 1, 0, 'C');
                    $pdf->Cell(80, 8, $detalle['USUARIO'], 1, 1, 'C');
                }
            } else {
                $pdf->Ln(2);
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 8, 'No hay detalles disponibles para esta licencia.', 0, 1);
            }

            // Separador visual
            $pdf->Ln(4);
            $pdf->Line(20, $pdf->GetY(), 195, $pdf->GetY());
        }
    } else {
        // Sin licencias
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
    $pdf->Cell(0, 10, 'EMBARGOS', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(8, 10, '#', 1, 0, 'C');
    $pdf->Cell(18, 10, 'Fecha', 1, 0, 'C');
    $pdf->Cell(25, 10, 'Monto', 1, 0, 'C');
    $pdf->Cell(130, 10, 'Descripción', 1, 1, 'C');

    if (!empty($listadoEmbargo)) {
        // Agregar las licencias al PDF
        foreach ($listadoEmbargo as $index => $embargo) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(8, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell(18, 10, $embargo['FECHA'], 1, 0, 'C');
            $pdf->Cell(25, 10, $embargo['MONTO'], 1, 0, 'C');
            $pdf->Cell(130, 10, $embargo['DESCRIPCION'], 1, 0, 'C');
        }
    } else {
        // Si no hay licencias, muestra un mensaje indicando que no hay licencias
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene Embargos.', 0, 1, 'C');
    }
}
if ($tipo_reporte == '2') {
    // Ajuste de la tabla para que ocupe el ancho completo de la página
    $anchoTotal = $pdf->getPageWidth();
    $anchoColumna = $anchoTotal / 8; // Ajustamos el número de columnas

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'SANCIONES', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    if (!empty($listadoSancion)) {
        $pdf->Cell(10, 10, '#', 1, 0, 'C');
        $pdf->Cell($anchoColumna, 10, 'Fecha Inicio', 1, 0, 'C');
        $pdf->Cell($anchoColumna, 10, 'Fecha Fin', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Cantidad de Días', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Tipo de Sanción', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Estado', 1, 1, 'C');


        // Agregar las licencias al PDF
        foreach ($listadoSancion as $index => $sancion) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(10, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell($anchoColumna, 10, $sancion['FECHA_INICIO'], 1, 0, 'C');
            $pdf->Cell($anchoColumna, 10, $sancion['FECHA_FIN'], 1, 0, 'C');
            $pdf->Cell(40, 10, $sancion['CANTIDAD_DIAS'], 1, 0, 'C');
            $pdf->Cell(40, 10, $sancion['TIPO'], 1, 0, 'C');
            $pdf->Cell(50, 10, $sancion['ESTADO'], 1, 1, 'C');
        }
    } else {
        // Si no hay SANCIONES, muestra un mensaje indicando que no hay 
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene Sanciones.', 0, 1, 'C');
    }
}
if ($tipo_reporte == '3') {
    // Ajuste de la tabla para que ocupe el ancho completo de la página
    $anchoTotal = $pdf->getPageWidth();
    $anchoColumna = $anchoTotal / 8; // Ajustamos el número de columnas

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'HORAS EXTRAS', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    if (!empty($listadoExtras)) {
        $pdf->Cell(10, 10, '#', 1, 0, 'C');
        $pdf->Cell($anchoColumna, 10, 'Fecha', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Cantidad de Horas', 1, 1, 'C');



        // Agregar las licencias al PDF
        foreach ($listadoExtras as $index => $extra) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(10, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell($anchoColumna, 10, $extra['FECHA'], 1, 0, 'C');
            $pdf->Cell(40, 10, $extra['CANTIDAD_HORAS'], 1, 0, 'C');
        }
    } else {
        // Si no hay Extras, muestra un mensaje indicando que no hay 
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene Horas Extras.', 0, 1, 'C');
    }
}
if ($tipo_reporte == '5') {
    // Ajuste de la tabla para que ocupe el ancho completo de la página
    $anchoTotal = $pdf->getPageWidth();
    $anchoColumna = $anchoTotal / 10; // Ajustamos el número de columnas

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'VIATICOS', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    if (!empty($listadoViatico)) {
        $pdf->Cell(10, 10, '#', 1, 0, 'C');
        $pdf->Cell($anchoColumna, 10, 'Fecha', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Tipo', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Monto', 1, 1, 'C');



        // Agregar las licencias al PDF
        foreach ($listadoViatico as $index => $viatico) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(10, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell($anchoColumna, 10, $viatico['FECHA_OTORGAMIENTO'], 1, 0, 'C');
            $pdf->Cell(40, 10, $viatico['TIPO_VIATICO'], 1, 0, 'C');
            $pdf->Cell(40, 10, $viatico['MONTO'], 1, 1, 'C');
        }
    } else {
        // Si no hay Extras, muestra un mensaje indicando que no hay 
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene Viaticos.', 0, 1, 'C');
    }
}
if ($tipo_reporte == '4') {
    // Ajuste de la tabla para que ocupe el ancho completo de la página
    $anchoTotal = $pdf->getPageWidth();
    $anchoColumna = $anchoTotal / 4; // Ajustamos el número de columnas

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'ASISTENCIA', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);

    if (!empty($listadoAsistencia)) {
        // Encabezados de la tabla
        $pdf->Cell(7, 10, '#', 1, 0, 'C');
        $pdf->Cell(20, 10, 'Fecha', 1, 0, 'C');
        $pdf->Cell(25, 10, 'Hora Entrada', 1, 0, 'C');
        $pdf->Cell(25, 10, 'Hora Salida', 1, 0, 'C');
        $pdf->Cell(15, 10, 'Estado', 1, 0, 'C');
        $pdf->Cell(100, 10, 'Observaciones', 1, 1, 'C');

        // Agregar las asistencias al PDF
        foreach ($listadoAsistencia as $index => $asistencia) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(7, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell(20, 10, $asistencia['FECHA'], 1, 0, 'C');
            $pdf->Cell(25, 10, $asistencia['HORA_ENTRADA'], 1, 0, 'C');
            $pdf->Cell(25, 10, $asistencia['HORA_SALIDA'], 1, 0, 'C');
            $pdf->Cell(15, 10, $asistencia['ESTADO'], 1, 0, 'C');
            $pdf->Cell(100, 10, $asistencia['OBSERVACIONES'], 1, 1, 'C');
        }

        // Espacio antes de mostrar el total de horas trabajadas
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Total de horas trabajadas en el mes actual: $totalHoras", 0, 1, 'L');
    } else {
        // Si no hay asistencias, muestra un mensaje
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene asistencias registradas.', 0, 1, 'C');
    }
}
if ($tipo_reporte == '7') {
    // Ajuste de la tabla para que ocupe el ancho completo de la página
    $anchoTotal = $pdf->getPageWidth();
    $anchoColumna = $anchoTotal / 4; // Ajustamos el número de columnas

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'VACACIONES', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);

    if (!empty($listadoVacaciones)) {
        // Encabezados de la tabla
        $pdf->Cell(7, 10, '#', 1, 0, 'C');
        $pdf->Cell(21, 10, 'Fecha Inicio', 1, 0, 'C');
        $pdf->Cell(18, 10, 'Fecha Fin', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Cantidad de Días', 1, 0, 'C');
        $pdf->Cell(10, 10, 'Año', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Días Restantes', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Estado', 1, 1, 'C');

        // Agregar las asistencias al PDF
        foreach ($listadoVacaciones as $index => $vacaciones) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(7, 10, $index + 1, 1, 0, 'C');
            $pdf->Cell(21, 10, $vacaciones['FECHA_INICIO'], 1, 0, 'C');
            $pdf->Cell(18, 10, $vacaciones['FECHA_FIN'], 1, 0, 'C');
            $pdf->Cell(30, 10, $vacaciones['CANTIDAD_DIAS'], 1, 0, 'C');
            $pdf->Cell(10, 10, $vacaciones['AÑO'], 1, 0, 'C');
            $pdf->Cell(30, 10, $vacaciones['VACACIONES_RESTANTES'], 1, 0, 'C');
            $pdf->Cell(30, 10, $vacaciones['ESTADO'], 1, 1, 'C');
        }
    } else {
        // Si no hay asistencias, muestra un mensaje
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'El empleado seleccionado no tiene vacaciones registradas.', 0, 1, 'C');
    }
}



// Salida del PDF
$pdf->Output('reporte_empleado_' . $empleado_id . '.pdf', 'D');
