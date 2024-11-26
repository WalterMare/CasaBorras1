<?php
session_start();

// Verifica si el usuario está logueado
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}
$mensaje = '';
require_once 'conexiondb.php';
$conexion = ConexionBD();

require_once 'select_tipoLicencia.php';
$listadoestado = Listar_Estado($conexion);

require_once 'select_empleado.php';
$listadolicencias = Listar_Licencia_Bis($conexion);
$listadoEmpleado = Listar_empleado_activos_bis($conexion);

require_once 'modificar_empleado.php';



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validar los datos antes de proceder

  $estilo = 'warning';

  // Verificamos si los datos obligatorios están presentes
  if (empty($_POST['tipo']) || empty($_POST['fecha']) || empty($_POST['dias']) || empty($_POST['estado']) || empty($_POST['empleado'])) {
    $mensaje = "Todos los campos obligatorios deben ser completados.";
  } else {
    // Datos capturados del formulario
    $tipoLicencia = $_POST['tipo'];
    $fechaInicio = $_POST['fecha'];
    $dias = $_POST['dias'];
    $estadoLicencia = $_POST['estado'];
    $empleadosSeleccionados = $_POST['empleado'];
    $fechainicial = new DateTime($fechaInicio);
    $fecha_final = $fechainicial->modify("+$dias days");
    $fecha_finalicima = $fecha_final->format('Y-m-d');
    // Validación de archivo (si se carga uno)
    $documento = '';
    if (isset($_FILES['documento']) && $_FILES['documento']['error'] == 0) {
      $documento = file_get_contents($_FILES['documento']['tmp_name']);
    }

    // Insertar en la base de datos
    try {
      mysqli_begin_transaction($conexion); // Iniciar la transacción

      // Insertar la licencia en la base de datos
      $sqlLicencia = "INSERT INTO licencia (fechainicio, fechafin, cantidaddias, IdTipo, IdUsuario, IdEstado)
                            VALUES (?, ?, ?, ?, ?, ?)";
      $stmtLicencia = $conexion->prepare($sqlLicencia);
      $stmtLicencia->execute([$fechaInicio, $fecha_finalicima, $dias, $tipoLicencia, $_SESSION['Usuario_Id'], $estadoLicencia]);

      // Obtener el ID de la licencia recién insertada
      $idLicencia = $conexion->insert_id;

      // Insertar los detalles de la licencia para cada empleado seleccionado
      foreach ($empleadosSeleccionados as $idEmpleado) {
        $descripcion = $_POST['descripcion']; // Asumimos que se recibe una descripción
        $sqlDetalleLicencia = "INSERT INTO detallelicencia (idLicencia, Descripcion, Documentacion, FechaCreacion, idEmpleado)
                                       VALUES (?, ?, ?, NOW(), ?)";
        $stmtDetalleLicencia = $conexion->prepare($sqlDetalleLicencia);
        $stmtDetalleLicencia->execute([$idLicencia, $descripcion, $documento, $idEmpleado]);

        //Cambiar el estado del empleado a 0 (por ejemplo, "en licencia")
        $estadoEmpleado = 0; // Estado para "en licencia"
        Modificar_EstadoLicencia_Empleado($idEmpleado, $estadoEmpleado, $conexion);
      }

      mysqli_commit($conexion); // Confirmar la transacción
      $mensaje = 'Licencia registrada exitosamente.';
      $estilo = 'success';
    } catch (Exception $e) {
      mysqli_rollBack($conexion); // Revertir si algo sale mal
      $mensaje = 'Error al registrar la licencia: ' . $e->getMessage();
      $estilo = 'danger';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
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
  <!--<link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
-->
  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
  <?php include_once 'partes/header.php' ?>
  <?php include_once 'partes/menu.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Registrar Licencias</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de movimientos</li>
          <li class="breadcrumb-item active">Registrar Licencias</li>
        </ol>
      </nav>
    </div>


    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Ingresa los datos</h5>
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-1"></i>
                Los campos indicados con (*) son requeridos
              </div>
              <div id='cartel' class="alert alert-<?php echo $estilo ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>
                <?php echo $mensaje; ?>
              </div>


              <form class="row g-3" method="post" enctype="multipart/form-data">
                <!-- Tipo de Licencia -->
                <div class="col-6">
                  <label for="tipo" class="form-label">Tipo de Licencia</label>
                  <select class="form-select" name="tipo" id="tipo" required>
                    <option value="">Selecciona una opción</option>
                    <?php foreach ($listadolicencias as $licencia) { ?>
                      <option value="<?php echo $licencia['ID']; ?>" <?php echo (!empty($_POST['tipo']) && $_POST['tipo'] == $licencia['ID']) ? 'selected' : ''; ?>>
                        <?php echo $licencia['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <!-- Empleados -->
                <div class="col-6">
                  <label for="empleados" class="form-label">Seleccione el/los empleados</label>
                  <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd;">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Seleccionar</th>
                          <th>Nombre</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($listadoEmpleado as $empleado) { ?>
                          <tr>
                            <td>
                              <input type="checkbox" name="empleado[]" value="<?php echo $empleado['ID']; ?>" <?php echo (!empty($_POST['empleado']) && in_array($empleado['ID'], $_POST['empleado'])) ? 'checked' : ''; ?>>
                            </td>
                            <td><?php echo $empleado['NOMBRE'] . " " . $empleado['APELLIDO']; ?></td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Fecha Inicio -->
                <div class="col-6">
                  <label for="fecha" class="form-label">Fecha Inicio (*)</label>
                  <input type="date" class="form-control" id="fecha" name="fecha" required>
                </div>

                <!-- Cantidad de Días -->
                <div class="col-6">
                  <label for="dias" class="form-label">Cantidad de Días (*)</label>
                  <input type="number" step="1" class="form-control" id="dias" name="dias" required>
                </div>

                <!-- Estado de la Licencia -->
                <div class="col-6">
                  <label for="estado" class="form-label">Estado de la Licencia (*)</label>
                  <select class="form-select" name="estado" id="estado" required>
                    <option value="">Selecciona una opción</option>
                    <?php foreach ($listadoestado as $estado) { ?>
                      <option value="<?php echo $estado['ID']; ?>" <?php echo (!empty($_POST['estado']) && $_POST['estado'] == $estado['ID']) ? 'selected' : ''; ?>>
                        <?php echo $estado['NOMBRE']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <!-- Descripción -->
                <div class="col-6">
                  <label for="descripcion" class="form-label">Descripción (*)</label>
                  <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
                </div>

                <!-- Documento Adicional -->
                <div class="col-6">
                  <label for="documento" class="form-label">Adjuntar Documento</label>
                  <input type="file" class="form-control" id="documento" name="documento" accept="image/*, .pdf">
                </div>

                <!-- Botón para Registrar -->
                <div class="col-12">
                  <button type="submit" class="btn btn-primary" name="BotonRegistrar">Registrar Licencia</button>
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

  <!-- Vendor JS Files
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script> -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>-->
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/js/cartel.js"></script>
  <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
</body>

</html>