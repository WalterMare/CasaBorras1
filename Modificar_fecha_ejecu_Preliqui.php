<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }


if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
require_once 'conexiondb.php';

$conn = ConexionBD();

// Obtener la fecha actual guardada
$sqlFecha = "SELECT fecha_preliquidacion FROM configuracion WHERE idconfiguracion = 1";
$result = $conn->query($sqlFecha);
$fecha = $result->fetch_assoc()['fecha_preliquidacion'];

// Guardar la nueva fecha si el formulario se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevaFecha = $_POST['fecha'];
    $sqlActualizar = "UPDATE configuracion SET fecha_preliquidacion = '$nuevaFecha' WHERE idconfiguracion = 1";
    if ($conn->query($sqlActualizar) === TRUE) {
        echo "Fecha de preliquidación actualizada.";
    } else {
        echo "Error al actualizar la fecha: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modificar Fecha Preliquidación</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">

    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

    <form class="row g-3" method="post">
        <label class="form-label" for="fecha">Fecha de Preliquidación:</label>
        <input class="form-control" type="date" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
        <button class="btn btn-primary" type="submit">Actualizar Fecha</button>
    </form>

    <script src="assets/js/cartel.js"></script>
</body>

</html>