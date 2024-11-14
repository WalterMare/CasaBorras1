<?php 
session_start();

// Verificación si la sesión está vacía y redirigir al login si es necesario
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}

require_once 'conexiondb.php';

// Manejo de errores en la conexión a la base de datos
try {
    $conexion = ConexionBD();
} catch (Exception $e) {
    die('Error en la conexión: ' . $e->getMessage());
}

require_once 'Actualizar_Estado_Empleado.php';
try {
    actualizarEstados($conexion);
} catch (Exception $e) {
    // Manejar errores de actualización de estado
    echo 'Error al actualizar los estados: ' . $e->getMessage();
}

require_once 'select_empleado.php';

try {
    // Listado de empleados y su cantidad
    $ListadoReporte = Listar_empleado($conexion);
    $CantidadEmpleados = count($ListadoReporte);

    // Listado de empleados activos e inactivos
    $ListadoReporte = Listar_empleado_activos($conexion);
    $CantidadEmpleadosActivos = count($ListadoReporte);

    $ListadoReporte = Listar_empleado_inactivos($conexion);
    $CantidadEmpleadosInactivos = count($ListadoReporte);
} catch (Exception $e) {
    die('Error al obtener los empleados: ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Casa Borras</title>
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

  <?php include_once 'partes/header.php'; ?>
  <?php include_once 'partes/menu.php'; ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Bienvenid@s!!</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active"><a href="index.php">Home</a></li>
        </ol>
      </nav>
    </div>

    <section class="section dashboard">
      <div class="row">

        <div class="col-lg-12">
          <div class="row">

            <!-- Tarjeta de mensaje de bienvenida -->
            <div class="col-xxl-12 col-xl-12">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Estás trabajando con el sistema de Casa Borras. <span>| ¡Muchos éxitos!</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="ps-3">
                      <img src="assets/img/img1.jpeg" alt="Logo de Casa Borras" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tarjetas de estadísticas de empleados -->
            <div class="col-xxl-4 col-xl-4">
              <div class="card info-card customers-card">
                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filtrar por</h6>
                    </li>
                    <li><a class="dropdown-item" href="#">Hoy</a></li>
                    <li><a class="dropdown-item" href="#">Este mes</a></li>
                    <li><a class="dropdown-item" href="#">Este año</a></li>
                  </ul>
                </div>
                <div class="card-body">
                  <h5 class="card-title">Total de Empleados</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $CantidadEmpleados ?></h6>
                      <span class="text-muted small pt-2 ps-1">Total de empleados de la empresa</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empleados activos -->
            <div class="col-xxl-4 col-xl-4">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Empleados Activos</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $CantidadEmpleadosActivos ?></h6>
                      <span class="text-danger small pt-1 fw-bold"><?php echo number_format((($CantidadEmpleadosActivos * 100) / $CantidadEmpleados), 2); ?>%</span> 
                      <span class="text-muted small pt-2 ps-1">de empleados activos</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empleados inactivos -->
            <div class="col-xxl-4 col-xl-4">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Empleados Inactivos</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $CantidadEmpleadosInactivos ?></h6>
                      <span class="text-danger small pt-1 fw-bold"><?php echo number_format((($CantidadEmpleadosInactivos * 100) / $CantidadEmpleados), 2); ?>%</span>
                      <span class="text-muted small pt-2 ps-1">de empleados inactivos</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </section>

  </main>

  <!-- Pie de página -->
  <?php include_once 'partes/footer.php'; ?>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Archivos JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>

</body>

</html>
