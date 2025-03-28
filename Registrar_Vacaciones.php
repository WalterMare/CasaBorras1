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

require_once 'Insertar_Vacacion.php';
require_once 'Validacion_vacaciones.php';
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
                        <label for="empleado" class="form-label">Empleado:</label>
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
                        <label for="dias" class="form-label">Cantidad de días que corresponde por la antigüedad</label>
                        <input class="form-control" type="text" id="dias" name="dias" readonly>
                    </div>


                    <div class="col-6">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                        <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio">
                    </div>

                    <div class="col-6">
                        <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                        <input class="form-control" type="date" id="fecha_fin" name="fecha_fin">
                    </div>

                    <div class="col-6">
                        <label for="año" class="form-label">Año:</label>
                        <input class="form-control" type="number" id="año" name="año">
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
                        <label for="estado" class="form-label">Estado</label>
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
            console.log("ID Empleado seleccionado:", empleadoId);

            if (empleadoId !== "") {
                var xhr = new XMLHttpRequest();
                var url = 'obtener_fecha_ingreso.php?empleado_id=' + empleadoId;
                console.log("URL de solicitud:", url);

                xhr.open('GET', url, true);
                xhr.onreadystatechange = function() {
                    console.log("Estado:", xhr.readyState, "Status:", xhr.status);

                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            console.log("Respuesta recibida:", xhr.responseText);

                            try {
                                var response = JSON.parse(xhr.responseText);
                                console.log("JSON parseado:", response);

                                if (response.success) {
                                    console.log("Fecha ingreso:", response.fecha_inicio);
                                    var diasVacaciones = calcularDias(response.fecha_inicio);
                                    console.log("Días calculados:", diasVacaciones);

                                    document.getElementById('dias').value = diasVacaciones + " días";
                                } else {
                                    console.error("Error del servidor:", response.message);
                                    document.getElementById('dias').value = "Error: " + response.message;
                                }
                            } catch (e) {
                                console.error("Error al parsear JSON:", e, "Respuesta:", xhr.responseText);
                                document.getElementById('dias').value = "Error en formato de datos";
                            }
                        } else {
                            console.error("Error HTTP:", xhr.status);
                            document.getElementById('dias').value = "Error de conexión (" + xhr.status + ")";
                        }
                    }
                };
                xhr.onerror = function() {
                    console.error("Error de red");
                    document.getElementById('dias').value = "Error de red";
                };
                xhr.send();
            } else {
                document.getElementById('dias').value = "";
            }
        }

        function calcularDias(fechaIngreso) {
            if (!fechaIngreso) return 0;

            try {
                var fechaActual = new Date();
                var fechaIngresoObj = new Date(fechaIngreso);

                // Validar que la fecha sea correcta
                if (isNaN(fechaIngresoObj.getTime())) {
                    console.error("Fecha inválida:", fechaIngreso);
                    return 0;
                }

                // Calcular diferencia en meses
                var mesesTrabajados = (fechaActual.getFullYear() - fechaIngresoObj.getFullYear()) * 12;
                mesesTrabajados += fechaActual.getMonth() - fechaIngresoObj.getMonth();

                // Ajustar si el día actual es menor al día de ingreso
                if (fechaActual.getDate() < fechaIngresoObj.getDate()) {
                    mesesTrabajados--;
                }

                console.log("Meses trabajados:", mesesTrabajados);

                // Calcular días según tiempo de servicio
                if (mesesTrabajados < 6) {
                    return 0; // Menos de 6 meses - no tiene vacaciones
                } else if (mesesTrabajados <= 12) {
                    return mesesTrabajados; // 6-12 meses - 1 día por mes
                } else {
                    // Más de 1 año - cálculo por antigüedad
                    var antiguedad = Math.floor(mesesTrabajados / 12);

                    if (antiguedad < 5) return 14; // 1-4 años
                    if (antiguedad < 10) return 21; // 5-9 años
                    if (antiguedad < 20) return 28; // 10-19 años
                    return 35; // 20+ años
                }
            } catch (e) {
                console.error("Error en cálculo de días:", e);
                return 0;
            }
        }

        // Función para calcular días entre fechas
        function calcularDiasEntreFechas() {
            var inicio = new Date(document.getElementById('fecha_inicio').value);
            var fin = new Date(document.getElementById('fecha_fin').value);

            // Validar que ambas fechas estén seleccionadas
            if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                return;
            }

            // Calcular diferencia en días (incluyendo ambos días)
            var diffTiempo = fin - inicio;
            var diffDias = Math.floor(diffTiempo / (1000 * 60 * 60 * 24)) + 1;

            // Asignar el valor calculado
            document.getElementById('cantidad_dias').value = diffDias > 0 ? diffDias : 0;

            // Validar contra días disponibles
            validarDiasDisponibles();
        }

        // Función para validar días disponibles
        function validarDiasDisponibles() {
            var diasDisponibles = parseInt(document.getElementById('dias').value) || 0;
            var diasSolicitados = parseInt(document.getElementById('cantidad_dias').value) || 0;
            var campoCantidad = document.getElementById('cantidad_dias');

            // Resetear estilo
            campoCantidad.style.color = '';
            campoCantidad.style.borderColor = '';

            // Validar si excede
            if (diasSolicitados > diasDisponibles) {
                campoCantidad.style.color = 'red';
                campoCantidad.style.borderColor = 'red';

                // Opcional: Mostrar mensaje de alerta
                alert(`¡Atención! Está solicitando ${diasSolicitados} días pero sólo tiene ${diasDisponibles} disponibles.`);
            }
        }
        // Función para calcular días restantes
        function calcularDiasRestantes() {
            var diasDisponibles = parseInt(document.getElementById('dias').value) || 0;
            var diasSolicitados = parseInt(document.getElementById('cantidad_dias').value) || 0;
            var campoRestantes = document.getElementById('vacaciones_restantes');

            // Calcular días restantes
            var diasRestantes = diasDisponibles - diasSolicitados;

            // Asegurarnos que no sea negativo
            diasRestantes = diasRestantes >= 0 ? diasRestantes : 0;

            // Actualizar el campo
            campoRestantes.value = diasRestantes;

            // Validar si excede (mantenemos la función anterior)
            validarDiasDisponibles();
        }

        // Modificar el evento de cantidad_dias para que llame a esta función
        document.getElementById('cantidad_dias').addEventListener('input', function() {
            calcularDiasRestantes();
            validarDiasDisponibles();
        });

        // También actualizar cuando cambian las fechas
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            calcularDiasEntreFechas();
            calcularDiasRestantes();
            var año = new Date(this.value).getFullYear();
            document.getElementById('año').value = año;
        });

        document.getElementById('fecha_fin').addEventListener('change', function() {
            calcularDiasEntreFechas();
            calcularDiasRestantes();
        });

        // Asignar eventos a los campos de fecha
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            calcularDiasEntreFechas();
            // Actualizar año automáticamente
            var año = new Date(this.value).getFullYear();
            document.getElementById('año').value = año;
        });

        document.getElementById('fecha_fin').addEventListener('change', calcularDiasEntreFechas);

        // Validar también cuando se modifica manualmente la cantidad
        document.getElementById('cantidad_dias').addEventListener('input', validarDiasDisponibles);

        // Al final de tu script, llama a la función para calcular valores iniciales
        window.addEventListener('DOMContentLoaded', function() {
            calcularDiasRestantes();
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            var diasDisponibles = parseInt(document.getElementById('dias').value) || 0;
            var diasSolicitados = parseInt(document.getElementById('cantidad_dias').value) || 0;

            if (diasSolicitados > diasDisponibles) {
                e.preventDefault();
                alert('No puede solicitar más días de los disponibles');
            }
        });

        
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