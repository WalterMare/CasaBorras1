<?php
function obtenerBlob($conexion, $licencia)
{

    $sql = "SELECT Documentacion FROM detallelicencia WHERE idLicencia = $licencia";
    $result = $conexion->query($sql);

    // Si encontramos el BLOB
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $blob = $row['Documentacion'];



        // Ruta para guardar el archivo
        $file_path = 'assets/archivos/licencia_' . $licencia . '.pdf';
        // Verificar si el directorio existe, si no, crearlo
        $directory = dirname($file_path);  // Obtiene la parte del directorio de la ruta

        if (!file_exists($directory)) {
            // Si no existe el directorio, crearlo (con permisos 0777)
            if (mkdir($directory, 0777, true)) {
                echo "Directorio creado exitosamente: " . $directory;
            } else {
                echo "Error al crear el directorio.";
                exit;
            }
        }
        // Guardar el BLOB en un archivo
        if (file_put_contents($file_path, $blob) !== false) {
            echo "Archivo guardado exitosamente en: " . $file_path;
        } else {
            echo "No se encontró el archivo.";
        }
    } 
    else {
        echo "No se encontró el archivo.<br>";
    }

    $conexion->close();
}
?>