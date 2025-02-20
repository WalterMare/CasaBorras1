<?php
if (isset($_GET['file_id'])) {
    // Conexión a la base de datos
    require_once 'conexiondb.php';
    $conexion = ConexionBD(); // Ajusta el nombre de tu archivo de conexión

    $file_id = intval($_GET['file_id']); // Convierte a entero para mayor seguridad

    // Consulta corregida: buscar en la tabla "documento"
    $query = "SELECT Documentacion FROM documento WHERE iddetalleLicencia = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $file_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $documento);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($documento) {
        // Configuración de cabeceras para la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="documentacion_licencia_' . $file_id . '.pdf"');
        echo $documento; // Envía el contenido del archivo
        exit;
    } else {
        echo "Documento no encontrado.";
    }
} else {
    echo "Parámetro no válido.";
}
?>

