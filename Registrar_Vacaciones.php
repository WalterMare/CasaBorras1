<?php
session_start();

// Verificar si el usuario está logueado
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

// Función para calcular los días de vacaciones según la antigüedad
function calcularDiasVacaciones($fechaIngreso)
{
    $fechaActual = new DateTime();
    $fechaIngreso = new DateTime($fechaIngreso);
    $antiguedad = $fechaIngreso->diff($fechaActual)->y;

    if ($antiguedad < 5) return 14;
    if ($antiguedad < 10) return 21;
    if ($antiguedad < 20) return 28;
    return 35;
}

// Obtener los años con días disponibles de un empleado
function obtenerAñosConVacacionesDisponibles($idEmpleado)
{
    $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');
    $stmt = $pdo->prepare("SELECT DISTINCT año FROM vacaciones WHERE idempleado = ? AND vacaciones_restantes > 0 ORDER BY año ASC");
    $stmt->execute([$idEmpleado]);
    $añosDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $añosDisponibles;
}

// Registrar las vacaciones cuando el formulario es enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');

    $idEmpleado = $_POST['idEmpleado'];
    $fechaInicio = $_POST['fecha_inicio'];
    $fechaFin = $_POST['fecha_fin'];
    $cantidadDias = $_POST['cantidad_dias'];
    $estado = $_POST['estado'];
    $año = $_POST['año'];

    // Obtener el año actual
    $añoActual = date('Y');

    // Verificar si el año seleccionado es futuro
    if ($año > $añoActual) {
        echo "No puedes registrar vacaciones para un año futuro.";
        exit;
    }

    // Obtener los días restantes de vacaciones de años anteriores
    $stmt = $pdo->prepare("SELECT SUM(vacaciones_restantes) FROM vacaciones WHERE idempleado = ? AND año <= ?");
    $stmt->execute([$idEmpleado, $año]);
    $diasRestantes = $stmt->fetchColumn();

    if ($diasRestantes === false) {
        $diasRestantes = 0;
    }

    // Calcular los días de vacaciones disponibles para el año actual
    $diasDisponibles = calcularDiasVacaciones($_POST['fecha_inicio']);
    $totalDiasDisponibles = $diasDisponibles + $diasRestantes;

    if ($totalDiasDisponibles >= $cantidadDias) {
        $nuevoSaldo = $totalDiasDisponibles - $cantidadDias;

        // Registrar las vacaciones
        $stmt = $pdo->prepare("INSERT INTO vacaciones (idempleado, fecha_inicio, fecha_fin, cantidad_dias, estado, año, vacaciones_restantes)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idEmpleado, $fechaInicio, $fechaFin, $cantidadDias, $estado, $año, $nuevoSaldo]);

        // Actualizar el remanente de días
        $stmt = $pdo->prepare("UPDATE vacaciones SET vacaciones_restantes = ? WHERE idempleado = ? AND año = ?");
        $stmt->execute([$nuevoSaldo, $idEmpleado, $año]);

        // Actualizar el estado del empleado a inactivo
        $stmt = $pdo->prepare("UPDATE empleado SET estado = 0 WHERE idempleado = ?");
        $stmt->execute([$idEmpleado]);

        echo "Vacaciones registradas correctamente.";
    } else {
        echo "No tienes suficientes días de vacaciones disponibles.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Borras</title>
    <link href="assets/css/style.css" rel="stylesheet">
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

    <script>
        window.onload = function() {
            const mensaje = "<?php echo $mensaje; ?>";
            if (mensaje) alert(mensaje);
        }
    </script>

    <title>Registrar Vacaciones</title>
    <script>
        function calcularDias() {
            const fechaInicioInput = document.getElementById('fecha_inicio').value;
            const fechaFinInput = document.getElementById('fecha_fin').value;
            const empleadoSelect = document.getElementById('idEmpleado');
            const diasDisponibles = parseInt(empleadoSelect.selectedOptions[0].getAttribute('data-dias')) || 0;

            if (fechaInicioInput && fechaFinInput) {
                const fechaInicio = new Date(fechaInicioInput);
                const fechaFin = new Date(fechaFinInput);
                // Calcular diferencia en días (incluyendo ambos días)
                const diff = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24) + 1;
                document.getElementById('cantidad_dias').value = diff;

                // Calcular remanente
                const remanente = diasDisponibles - diff;
                document.getElementById('remanente_dias').value = remanente >= 0 ? remanente : 0;

                // Validar que no se seleccione más días de los disponibles
                if (diff > diasDisponibles) {
                    alert('No tienes suficientes días de vacaciones disponibles.');
                    // Opcional: deshabilitar el botón de envío para prevenir el registro
                    document.querySelector("button[type='submit']").disabled = true;
                } else {
                    // Habilitar el botón si la cantidad es correcta
                    document.querySelector("button[type='submit']").disabled = false;
                }
            }
        }


        function actualizarDiasDisponibles() {
            const selectEmpleado = document.getElementById('idEmpleado');
            const diasDisponibles = parseInt(selectEmpleado.selectedOptions[0].getAttribute('data-dias')) || 0;

            document.getElementById('remanente_dias').value = diasDisponibles;
        }


        // Función para actualizar los años disponibles cuando se selecciona un empleado
        function actualizarAñosDisponibles() {
            const empleadoSelect = document.getElementById('idEmpleado');
            const idEmpleado = empleadoSelect.value;

            if (idEmpleado) {
                fetch(`get_anos_disponibles.php?idempleado=${idEmpleado}`)
                    .then(response => response.json())
                    .then(data => {
                        const añoSelect = document.getElementById('año');
                        añoSelect.innerHTML = ''; // Limpiar los años actuales

                        // Obtener el año actual
                        const añoActual = new Date().getFullYear();

                        // Filtrar los años para que no se incluyan años futuros
                        data = data.filter(año => año <= añoActual);

                        // Crear las opciones de años disponibles
                        data.forEach(año => {
                            const option = document.createElement('option');
                            option.value = año;
                            option.textContent = año;
                            añoSelect.appendChild(option);
                        });

                        // Seleccionar el año actual por defecto si está disponible
                        if (data.includes(añoActual)) {
                            añoSelect.value = añoActual;
                        }
                    });
            }
        }
    </script>
</head>

<body>
    <?php include_once 'partes/header.php'; ?>
    <?php include_once 'partes/menu.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Registrar Vacaciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de movimientos</li>
                    <li class="breadcrumb-item active">Registrar Vacaciones</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ingrese los datos</h5>
                            <form class="row g-3" action="registrar_vacaciones.php" method="post">
                                <div class="col-6">
                                    <label class="form-label" for="idEmpleado">Seleccione un empleado:</label>
                                    <select class="form-select" id="idEmpleado" name="idEmpleado" required onchange="actualizarAñosDisponibles()">
                                        <option value="">Seleccione...</option>
                                        <?php
                                        // Conexión a la base de datos
                                        $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');
                                        $stmt = $pdo->query("SELECT idempleado, nombre, apellido, fecha_inicio FROM empleado WHERE estado = 1");

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $diasVacaciones = calcularDiasVacaciones($row['fecha_inicio']);
                                            echo "<option value='{$row['idempleado']}' data-dias='{$diasVacaciones}'>";
                                            echo "{$row['nombre']} {$row['apellido']} - {$diasVacaciones} días";
                                            echo "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="form-label">Año:</label>
                                    <select class="form-select" id="año" name="año" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="form-label">Fecha Inicio:</label>
                                    <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" required onchange="calcularDias()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Fecha Fin:</label>
                                    <input class="form-control" type="date" id="fecha_fin" name="fecha_fin" required onchange="calcularDias()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Cantidad de Días:</label>
                                    <input class="form-control" type="number" id="cantidad_dias" name="cantidad_dias" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Remanente de Días:</label>
                                    <input class="form-control" type="number" id="remanente_dias" name="remanente_dias" value="0" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Estado:</label>
                                    <select class="form-select" name="estado" required>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Aprobado">Aprobado</option>
                                        <option value="Rechazado">Rechazado</option>
                                    </select>
                                </div>
                                <div class="text-center">
                                    <button class="btn btn-primary" type="submit">Registrar Vacaciones</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once 'partes/footer.php'; ?>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>