<?php
session_start();

// Verificar si la sesión está activa
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

// Incluir la conexión a la base de datos
require_once 'conexiondb.php';
$conexion = ConexionBD();





require_once 'select_empleado.php';
$listadoEmpleado = Listar_empleado($conexion);
$CantidadEmpleado = count($listadoEmpleado);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Vacaciones</title>
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
    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php' ?>
    <!-- End Header -->
    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>

    <!-- End Sidebar-->
    <main id="main" class="main ">

        <div class="pagetitle">
            <h1>Vacaciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de movimientos</li>
                    <li class="breadcrumb-item active">Vacaciones</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Registro de Vacaciones</h5>

                <?php
                $Mensaje = '';
                $Estilo = 'warning';
                if (!empty($_POST['BotonRegistrar'])) {
                    //estoy en condiciones de poder validar los datos
                    $Mensaje = Validar_Datos();
                    if (empty($Mensaje)) {
                        if (InsertarVacacion($conexion) != false) {
                            $Mensaje = 'Se ha registrado correctamente.';
                            $_POST = array();
                            $Estilo = 'success';
                        } ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-1"></i>
                            <?php echo $Mensaje; ?>
                        </div>
                    <?php  } else { ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <?php echo $Mensaje; ?>
                        </div><?php }
                        } ?>




                <form class="row g-3" method="POST">
                    <div class="col-6">
                        <label  for="empleado" class="form-label">Empleado:</label>
                        <select class="form-select" aria-label="Selector" id="empleado" name="empleado" onchange="calcularDiasVacaciones()"> 
                            <option value="">Selecciona una opcion</option>
                            <?php
                            $selected = '';
                            for ($i = 0; $i < $CantidadEmpleado; $i++) {
                                if (!empty($_POST['empleado']) && $_POST['empleado'] ==  $listadoEmpleado[$i]['ID']) { //recuerda el elemento seleccionado
                                    $selected = 'selected';
                                } else {
                                    $selected = ''; //limpia la variable para que solo se seleccione una opcion
                                }
                            ?>
                                <option value="<?php echo $listadoEmpleado[$i]['ID']; ?>" <?php echo $selected; ?>>
                                    <?php echo $listadoEmpleado[$i]['NOMBRE'] . " " . $listadoEmpleado[$i]['APELLIDO']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-6">
                    <label for="dias" class="form-label">Cantidad de días que corresponde por la antiguedad</label>
                    <input class="form-control" type="date" id="dias" name="dias" readonly >
                    </div>


                    <div class="col-6">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                        <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" >
                    </div>

                    <div class="col-6">
                        <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                        <input class="form-control" type="date" id="fecha_fin" name="fecha_fin" >
                    </div>

                    <div class="col-6">
                        <label for="año" class="form-label">Año:</label>
                        <input class="form-control" type="date" id="año" name="año" >
                    </div>
                    <div class="col-6">
                        <label for="cantidad_dias" class="form-label">Cantidad de Días:</label>
                        <input class="form-control" type="number" id="cantidad_dias" name="cantidad_dias">
                    </div>

                    <div class="col-6">
                        <label for="vacaciones_restantes" class="form-label">Vacaciones Restantes:</label>
                        <input class="form-control" type="number" id="vacaciones_restantes" name="vacaciones_restantes">
                    </div>
                    <div class="col-6">
                    <select class="form-select" name="estado" id="estado">
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                    </select>
                    </div>
                 
                    <div class="col-6">
                        <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
<script>
    function calcularDiasVacaciones() {
    var empleadoId = document.getElementById('empleado').value;

    // Verificamos que se haya seleccionado un empleado
    if (empleadoId !== "") {
        // Realizamos una solicitud AJAX para obtener la fecha de ingreso
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'obtener_fecha_ingreso.php?empleado_id=' + empleadoId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var fechaIngreso = xhr.responseText;
                var diasVacaciones = calcularDias(fechaIngreso);
                document.getElementById('dias').value = diasVacaciones;
            }
        };
        xhr.send();
    } else {
        document.getElementById('dias').value = "";
    }
}

function calcularDias(fechaIngreso) {
    var fechaActual = new Date();
    var fechaIngreso = new Date(fechaIngreso);
    var antiguedad = fechaActual.getFullYear() - fechaIngreso.getFullYear();
    
    // Calcular los días de vacaciones según la antigüedad
    if (antiguedad < 5) return 14;
    if (antiguedad < 10) return 21;
    if (antiguedad < 20) return 28;
    return 35;
}
</script>
    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php' ?>
    <!-- End Footer -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/js/cartel.js"></script>
    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>