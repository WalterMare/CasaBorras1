<?php
   require_once "conexiondb.php";

   $conexion= ConexionBD();

    require_once ("select_UltimosEmpleados.php");
    $datos =Listar_ultimosEmpleados2($conexion,1);

    require_once('TCPDF-main/tcpdf.php');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Casa Borras S.A');
    $pdf->SetTitle('Reporte');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    if (@file_exists(dirname('FILE') . '/lang/eng.php')) {
        require_once(dirname('FILE') . '/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    $pdf->SetFont('helvetica', '', 11);

    $pdf->AddPage();
    
    $html = '
        <style>
            h1{
                font-family: Arial, Helvetica, sans-serif;
            }
        </style>
        <img src="assets/img/LOGO2.jpg">
        <h1>REPORTE</h1>
        <h3>ÚLTIMOS EMPLEADOS REGISTRADOS</h3>
        <br><br>
    ';

    $html.='
        <style>
            table {
                border-collapse: collapse;
                margin-top: 100px;
            }
            th{
                vertical-align:middle;
            }

            table, th, td {
                border: 1px solid black;
            }
            table > tr > th {
                font-weight: bold; 
                text-align: center;
                vertical-align: middle;
                color: black;
                height: 40px;
            }

            table > tr > td {
                font-weight: bold; 
                text-align: center;
                color: black;
                height: 40px;
            }
        </style>
        <p align="center"> Fecha: <? php echo(date("d-m-Y"));?>
						</p>
        <table>
            <tr>    
                <th scope="col">EMPLEADO</th>
                <th scope="col">FECHA DE INICIO</th>
                <th scope="col">ESTADO</th>
                <th scope="col">CARGO</th>
            </tr>';

            foreach ($datos as $row) {
                $html.= 
                '<tr>
                    <td>' . $row['NOMBRE'] ." ". $row['APELLIDO'] .  '</td>
                    <td>' . $row['FECHA_INICIO'] . '</td>
                    <td>' . $row['ESTADO']. '</td>
                    <td>' . $row['CARGO'] . '</td>
                </tr>';
            }

    $html.=' 
            </table>';

    $pdf->writeHTML($html, true, false, false, false, 'C');

    // move pointer to last page
    $pdf->lastPage();
    ob_end_clean();
    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output('Reporte.pdf', 'I');
?>