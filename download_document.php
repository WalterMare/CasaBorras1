<?php
if (isset($_GET['file_id'])) {
    // Conexión a la base de datos
    require_once 'conexiondb.php';
// Conexión a la base de datos
    $conexion = ConexionBD(); // Ajusta el nombre de tu archivo de conexión

    $file_id = intval($_GET['file_id']); // Sanitiza el parámetro recibido

    // Consulta para obtener el documento
    $query = "SELECT documentacion FROM detallelicencia WHERE idLicencia = $file_id";
    $result = mysqli_query($conexion, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Configuración de cabeceras para la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="documentacion_licencia_' . $file_id . '.pdf"');
        echo $row['documentacion']; // Envía el contenido del archivo
        exit;
    } else {
        echo "Documento no encontrado.";
    }
} else {
    echo "Parámetro no válido.";
}
?>
