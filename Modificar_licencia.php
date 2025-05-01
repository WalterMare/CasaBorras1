<?php
session_start();
require_once 'conexiondb.php';

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

$conexion = ConexionBD();
$idLicencia = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idLicencia == 0) {
    echo "ID de licencia inválido.";
    exit;
}

// Obtener los datos de la licencia
$sql = "SELECT * FROM licencia WHERE idlicencia = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idLicencia);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$licencia = mysqli_fetch_assoc($resultado);

// Obtener tipos de licencia y estados
$tipos = mysqli_query($conexion, "SELECT * FROM tipolicencia");
$estados = mysqli_query($conexion, "SELECT * FROM estadolicencia");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechainicio = $_POST['fechainicio'];
    $fechafin = $_POST['fechafin'];
    $idtipo = (int)$_POST['idtipo'];
    $idestado = (int)$_POST['idestado'];
    $cantidaddias = (int)$_POST['cantidaddias'];

    $sqlUpdate = "UPDATE licencia SET fechainicio = ?, fechafin = ?, IdTipo = ?, IdEstado = ?, cantidaddias = ? WHERE idlicencia = ?";
    $stmtUpdate = mysqli_prepare($conexion, $sqlUpdate);
    mysqli_stmt_bind_param($stmtUpdate, "ssiiii", $fechainicio, $fechafin, $idtipo, $idestado, $cantidaddias, $idLicencia);

    if (mysqli_stmt_execute($stmtUpdate)) {
        header("Location: Licencias.php");
        exit;
    } else {
        echo "Error al actualizar la licencia.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Licencia</title>
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

    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1> Licencias</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Gestor Movimientos</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="container mt-4">
                                <h5>Modificar Licencia</h5>
                                <form class="row g-3" method="post">
                                    <div class="col-6">
                                        <label class="form-label">Fecha de Inicio:</label>
                                        <input type="date" name="fechainicio" class="form-control" value="<?php echo $licencia['fechainicio']; ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Fecha de Fin:</label>
                                        <input type="date" name="fechafin" class="form-control" value="<?php echo $licencia['fechafin']; ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Tipo de Licencia:</label>
                                        <select name="idtipo" class="form-select">
                                            <?php while ($tipo = mysqli_fetch_assoc($tipos)) { ?>
                                                <option value="<?php echo $tipo['idtipoLicencia']; ?>"
                                                    <?php echo $licencia['IdTipo'] == $tipo['idtipoLicencia'] ? 'selected' : ''; ?>>
                                                    <?php echo $tipo['descripcion']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label">Estado de Licencia:</label>
                                        <select name="idestado" class="form-select">
                                            <?php while ($estado = mysqli_fetch_assoc($estados)) { ?>
                                                <option value="<?php echo $estado['idestadoLicencia']; ?>"
                                                    <?php echo $licencia['IdEstado'] == $estado['idestadoLicencia'] ? 'selected' : ''; ?>>
                                                    <?php echo $estado['nombreEstado']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label">Cantidad de Días:</label>
                                        <input type="number" name="cantidaddias" class="form-control" value="<?php echo $licencia['cantidaddias']; ?>" required>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                                        <a href="Licencias.php" class="btn btn-secondary mt-3">Cancelar</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>


    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let fechainicio = document.querySelector('input[name="fechainicio"]');
            let fechafin = document.querySelector('input[name="fechafin"]');
            let cantidaddias = document.querySelector('input[name="cantidaddias"]');

            function calcularDias() {
                if (fechainicio.value && fechafin.value) {
                    let inicio = new Date(fechainicio.value);
                    let fin = new Date(fechafin.value);

                    if (fin >= inicio) {
                        let diffTiempo = fin.getTime() - inicio.getTime();
                        let diffDias = Math.ceil(diffTiempo / (1000 * 60 * 60 * 24)) + 1; // +1 para contar el primer día
                        cantidaddias.value = diffDias;
                    } else {
                        cantidaddias.value = 0;
                    }
                }
            }

            fechainicio.addEventListener("change", calcularDias);
            fechafin.addEventListener("change", calcularDias);
        });
    </script>


</body>

</html>