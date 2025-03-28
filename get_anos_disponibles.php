<?php
header('Content-Type: application/json');

if (isset($_GET['idempleado'])) {
    $idempleado = $_GET['idempleado'];

    try {
        // Nueva conexión PDO
        $host     = 'localhost';
        $dbname   = 'recursoshumanos';
        $username = 'root';
        $password = '12345';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener el año actual
        $añoActual = date('Y');

        // Devolver solo el año actual
        echo json_encode([$añoActual]);
        
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        echo json_encode([]);
    }
}
?>




