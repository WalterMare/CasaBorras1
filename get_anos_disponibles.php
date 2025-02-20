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

        // Consulta para obtener la fecha de inicio del empleado
        $stmt = $pdo->prepare("SELECT fecha_inicio FROM empleado WHERE idempleado = ?");
        $stmt->execute([$idempleado]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            $fechaIngreso = new DateTime($empleado['fecha_inicio']);
            $añoIngreso = $fechaIngreso->format('Y');
            $añoActual = date('Y');

            // Crear un array con los años desde el año de ingreso hasta el año actual
            $añosDisponibles = [];
            for ($año = $añoIngreso; $año <= $añoActual; $año++) {
                $añosDisponibles[] = $año;
            }

            echo json_encode($añosDisponibles);
        } else {
            echo json_encode([]);
        }
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        echo json_encode([]);
    }
}
?>



