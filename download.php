<?php
if (isset($_GET['file'])) {
    $file = base64_decode($_GET['file']); // Decodificar el identificador

    // Generar el encabezado para forzar la descarga
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="documentacion_licencia.pdf"');
    header('Content-Length: ' . strlen($file));

    echo $file; // Enviar el contenido al cliente
    exit;
} else {
    echo "Archivo no encontrado.";
    exit;
}
?>
