<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';

// Conexión a la base de datos
$conexion = ConexionBD();

// Inicialización de variables para mensajes
$mensaje = '';
$estilo = 'info';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
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

    <title>Registrar Licencias y Detalles</title>
    <script>
        function agregarDetalle() {
            const container = document.getElementById('detallesContainer');
            const detalleHTML = `
        <br>
        <div class="detalle">
            <div class="col-6">
                <label class="form-control">Descripción del Detalle:</label>
                <input class="form-control" type="text" name="detalles_descripcion[]" required>
            </div>
            <div class="col-6">
                <label class="form-control">Documentación:</label>
                <input class="form-control" type="file" name="detalles_documentacion[]" accept="application/pdf">
            </div>
            <button class="btn btn-primary" type="button" onclick="eliminarDetalle(this)">Eliminar</button>
        </div>`;
            container.insertAdjacentHTML('beforeend', detalleHTML);
        }


        function eliminarDetalle(button) {
            button.parentElement.remove();
        }
    </script>
</head>

<body>

    <h5 class="card-title">Ingresa los datos</h5>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Los campos indicados con (*) son requeridos
    </div>


    <form class="row g-3" action="Registrar_Licencia.php" id="formLicencia" method="post" enctype="multipart/form-data">
        <div class="col-6">
            <!-- Selección de Empleado -->
            <label class="form-label" for="idEmpleado">Seleccione un empleado:</label>
            <select class="form-select" id="idEmpleado" name="idEmpleado" required>
                <?php
                // Conexión a la base de datos
                $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');
                $stmt = $pdo->query("SELECT idempleado, nombre, apellido FROM empleado WHERE estado = 1");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['idempleado']}'>{$row['nombre']} {$row['apellido']}</option>";
                }
                ?>
            </select>
        </div>


        <div class="col-6">
            <!-- Información de la Licencia -->
            <label class="form-label" for="fechainicio">Fecha de Inicio:</label>
            <input class="form-control" type="date" id="fechainicio" name="fechainicio" required><br>
        </div>

        <div class="col-6">
            <label class="form-label" for="fechafin">Fecha de Fin:</label>
            <input class="form-control" type="date" id='fechafin' name="fechafin" required><br>
        </div>
        <div class="col-6">
            <label class="form-label" for="diasLicencia">Cantidad de días:</label>
            <input class="form-control" type="number" id="diasLicencia" name="diasLicencia" readonly>
        </div>
        <div id="errorDias" class="text-danger"></div>

        <div class="col-6">
            <label class="form-label" for="IdTipo">Tipo de Licencia:</label>
            <select class="form-select" id='IdTipo' name="IdTipo" required>
                <?php
                $pdo = new PDO('mysql:host=localhost;dbname=recursoshumanos', 'root', '12345');
                $stmt = $pdo->query("SELECT t.idtipoLicencia, t.descripcion, d.dias_maximos 
                                 FROM tipolicencia t
                                 LEFT JOIN dias_maximos_licencia d 
                                 ON t.idtipoLicencia = d.idtipoLicencia");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['idtipoLicencia']}' data-dias-maximos='{$row['dias_maximos']}'>
                        {$row['descripcion']}
                      </option>";
                }
                ?>
            </select><br>
            <div id="diasMaximosLeyenda" class="form-text text-primary"></div>
        </div>
        <div class="col-6">
            <label class="form-label" for="IdTipo">Fecha de parto estimada</label>
            <input class="form-control" type="date" id="fechaProbableParto" name="fechaProbableParto" value="<?php echo htmlspecialchars($fechaProbablePartoDesdeBD); ?>">
        </div>

        <div class="col-6">
            <label class="form-label" for="IdEstado">Estado de la Licencia:</label>
            <select class="form-select" id="IdEstado" name="IdEstado" required>
                <?php
                $stmt = $pdo->query("SELECT idestadoLicencia, nombreEstado FROM estadolicencia");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['idestadoLicencia']}'>{$row['nombreEstado']}</option>";
                }
                ?>
            </select><br><br>
        </div>
        <br>
        <div class="col-2">
            <label class="form-label" for="usuario">Usuario que otorga:</label>
            <input class="form-control" type="text" id="usuario" name="usuario" value="<?php echo $_SESSION['Usuario_Id'] ?>" readonly>
        </div>
        <!-- Detalles de Licencia -->
        <h2>Detalles de la Licencia</h2>
        <div id="detallesContainer">
            <!-- Detalle inicial -->
            <div class="detalle">
                <div class="col-6">
                    <label class="form-control" for="detalles_descripcion[]">Descripción del Detalle:</label>
                    <input class="form-control" type="text" id="detalles_descripcion[]" name="detalles_descripcion[]" required>
                </div>
                <br>
                <div class="col-6">
                    <label class="form-control" for="detalles_documentacion[]">Documentación:</label>
                    <input class="form-control" type="file" id="detalles_documentacion[]" name="detalles_documentacion[]" accept="application/pdf">
                </div>
                <br>


            </div>
        </div>
        <div class="text-center">
            <button type="button" class="btn btn-secondary" onclick="agregarDetalle()">Agregar Otro Items</button><br><br>
            <button class="btn btn-primary" id="btnRegistrar" name='BotonRegistrar' type="submit">Registrar Licencia</button>
        </div>
    </form>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fechainicio');
            const fechaFin = document.getElementById('fechafin');
            const diasLicencia = document.getElementById('diasLicencia');
            const tipoLicenciaSelect = document.getElementById('IdTipo');
            const leyendaDias = document.getElementById('diasMaximosLeyenda');
            const btnRegistrar = document.getElementById('btnRegistrar');
            const errorDias = document.getElementById('errorDias');
            const formLicencia = document.getElementById('formLicencia');
            const fechaProbableParto = document.getElementById('fechaProbableParto');

            // Calcula los días entre fechas
            function calcularDias() {
                if (fechaInicio.value && fechaFin.value) {
                    const inicio = new Date(fechaInicio.value);
                    const fin = new Date(fechaFin.value);
                    const diferencia = (fin - inicio) / (1000 * 60 * 60 * 24) + 1;
                    diasLicencia.value = diferencia >= 0 ? diferencia : 0;
                    validarTodo();
                }
            }

            // Valida licencias que no son maternidad
            function validarDiasMaximos() {
                const selectedOption = tipoLicenciaSelect.options[tipoLicenciaSelect.selectedIndex];
                const diasMaximos = parseInt(selectedOption.getAttribute('data-dias-maximos')) || 0;
                const diasSeleccionados = parseInt(diasLicencia.value) || 0;

                if (tipoLicenciaSelect.value === '1') return validarLicenciaMaternidad();

                if (diasMaximos && diasSeleccionados > diasMaximos) {
                    errorDias.textContent = `⚠️ La cantidad de días supera el máximo permitido de ${diasMaximos} día(s).`;
                    return false;
                }

                errorDias.textContent = '';
                return true;
            }

            // Valida licencia de maternidad
            function validarLicenciaMaternidad() {
                if (tipoLicenciaSelect.value !== '1') return true;
                if (!fechaInicio.value || !fechaFin.value || !fechaProbableParto.value) return false;

                const inicio = new Date(fechaInicio.value);
                const fin = new Date(fechaFin.value);
                const parto = new Date(fechaProbableParto.value);

                const diasSeleccionados = (fin - inicio) / (1000 * 60 * 60 * 24) + 1;
                const diasAntesParto = Math.floor((parto - inicio) / (1000 * 60 * 60 * 24)) + 1;
                const diasDespuesParto = diasSeleccionados - diasAntesParto;

                if (diasSeleccionados !== 90) {
                    errorDias.textContent = "⚠️ La licencia por maternidad debe ser exactamente de 90 días corridos.";
                    return false;
                }

                if (!((diasAntesParto === 45 && diasDespuesParto === 45) ||
                        (diasAntesParto === 30 && diasDespuesParto === 60))) {
                    errorDias.textContent = "⚠️ Solo se permiten 45 días antes + 45 después o 30 antes + 60 después.";
                    return false;
                }

                errorDias.textContent = '';
                return true;
            }

            // Calcula automáticamente fechaFin para maternidad
            function calcularFechaFin() {
                if (tipoLicenciaSelect.value !== '1') return;
                if (!fechaInicio.value || !fechaProbableParto.value) return;

                const inicio = new Date(fechaInicio.value);
                const parto = new Date(fechaProbableParto.value);

                let diasAntesParto = Math.floor((parto - inicio) / (1000 * 60 * 60 * 24)) + 1;
                let diasDespuesParto;

                // Ajustar según combinación permitida
                if (diasAntesParto >= 45) {
                    diasAntesParto = 45;
                    diasDespuesParto = 45;
                } else {
                    diasAntesParto = 30;
                    diasDespuesParto = 60;
                }

                const fechaFinal = new Date(inicio);
                fechaFinal.setDate(inicio.getDate() + diasAntesParto + diasDespuesParto - 1);

                fechaFin.value = fechaFinal.toISOString().split('T')[0];
                calcularDias();
            }

            // Valida todo el formulario
            function validarTodo() {
                let esValido = true;
                errorDias.textContent = '';

                if (!fechaInicio.value || !fechaFin.value) esValido = false;

                if (tipoLicenciaSelect.value === '1') {
                    if (!validarLicenciaMaternidad()) esValido = false;
                } else {
                    if (!validarDiasMaximos()) esValido = false;
                }

                btnRegistrar.disabled = !esValido;
                return esValido;
            }

            // Eventos
            fechaInicio.addEventListener('change', function() {
                if (tipoLicenciaSelect.value === '1') {
                    calcularFechaFin(); // maternidad
                } else {
                    calcularDias(); // otras licencias
                }
            });

            fechaFin.addEventListener('change', function() {
                if (tipoLicenciaSelect.value !== '1') {
                    calcularDias(); // solo recalcula para no maternidad
                }
            });

            fechaProbableParto.addEventListener('change', calcularFechaFin);

            tipoLicenciaSelect.addEventListener('change', function() {
                const selectedOption = tipoLicenciaSelect.options[tipoLicenciaSelect.selectedIndex];
                if (selectedOption.value === '1') {
                    leyendaDias.textContent = "La licencia por maternidad es de 90 días corridos. Debe tomar al menos 30 días antes del parto.";
                    calcularFechaFin();
                } else {
                    const diasMaximos = parseInt(selectedOption.getAttribute('data-dias-maximos')) || 0;
                    leyendaDias.textContent = diasMaximos ? `👉 Esta licencia permite un máximo de ${diasMaximos} día(s).` : '';
                    calcularDias();
                }
                validarTodo();
            });

            formLicencia.addEventListener('submit', function(e) {
                if (!validarTodo()) e.preventDefault();
            });
        });
    </script>
    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>