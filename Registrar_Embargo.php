<?php

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();



require_once 'select_empleado.php';
$listadoEmpleado = Listar_empleado($conexion);
$CantidadEmpleado = count($listadoEmpleado);


require_once 'validacion_registro_Embargo.php';
require_once 'Insertar_Embargo.php';


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


  <h5 class="card-title">Ingresa los datos</h5>
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle me-1"></i>
    Los campos indicados con (*) son requeridos
  </div>

  <?php
  $Mensaje = '';
  $Estilo = 'warning';
  if (!empty($_POST['BotonRegistrar'])) {
    //estoy en condiciones de poder validar los datos
    $Mensaje = Validar_Datos();
    if (empty($Mensaje)) {
      if (InsertarEmbargo($conexion) != false) {
        $Mensaje = 'Se ha registrado correctamente.';
        $_POST = array();
        $Estilo = 'success';
      } ?>
      <div id='cartel' class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo $Mensaje; ?>
      </div>
    <?php  } else { ?>
      <div id='cartel' class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo $Mensaje; ?>
      </div><?php }
        } ?>


  <form class="row g-3" method="post">

    <!-- Empleado -->
    <div class="col-6">
      <label for="selector" class="form-label">Empleado(*)</label>
      <select class="form-select" id="selector" name="empleado" required>
        <option value="">Selecciona una opción</option>
        <?php
        for ($i = 0; $i < $CantidadEmpleado; $i++) {
          $selected = (!empty($_POST['empleado']) && $_POST['empleado'] == $listadoEmpleado[$i]['ID']) ? 'selected' : '';
        ?>
          <option value="<?php echo $listadoEmpleado[$i]['ID']; ?>" <?php echo $selected; ?>>
            <?php echo $listadoEmpleado[$i]['NOMBRE'] . " " . $listadoEmpleado[$i]['APELLIDO']; ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <!-- Fecha de alta (registro) -->
    <div class="col-6">
      <label for="fecha" class="form-label">Fecha de Registro(*)</label>
      <input type="date" class="form-control" id="fecha" name="fecha" required>
    </div>

    <!-- Expediente -->
    <div class="col-6">
      <label for="expediente" class="form-label">Nro. Expediente(*)</label>
      <input type="text" class="form-control" id="expediente" name="expediente" required>
    </div>

    <!-- Tipo -->
    <div class="col-6">
      <label for="tipo" class="form-label">Tipo de Embargo(*)</label>
      <select class="form-select" id="tipo" name="tipo" required>
        <option value="">Selecciona...</option>
        <option value="Alimentos">Alimentos</option>
        <option value="Bancario">Bancario</option>
        <option value="AFIP">AFIP</option>
        <option value="Otro">Otro</option>
      </select>
    </div>

    <!-- Fechas de vigencia -->
    <div class="col-6">
      <label for="fecha_inicio" class="form-label">Fecha Inicio(*)</label>
      <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
    </div>

    <div class="col-6">
      <label for="fecha_fin" class="form-label">Fecha Fin</label>
      <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
      <small class="text-muted">Dejar vacío si es indefinido</small>
    </div>

    <!-- Monto fijo -->
    <div class="col-6">
      <label for="monto" class="form-label">Monto Fijo Mensual</label>
      <input type="number" step="0.01" class="form-control" id="monto" name="monto">
    </div>

    <!-- Porcentaje -->
    <div class="col-6">
      <label for="porcentaje" class="form-label">% del Sueldo</label>
      <input type="number" step="0.01" class="form-control" id="porcentaje" name="porcentaje">
      <small class="text-muted">Ejemplo: 20 = 20%</small>
    </div>

    <!-- Estado -->
    <div class="col-6">
      <label for="estado" class="form-label">Estado</label>
      <select class="form-select" id="estado" name="estado">
        <option value="1" selected>Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>

    <!-- Descripción -->
    <div class="col-12">
      <label for="descripcion" class="form-label">Descripción(*)</label>
      <input type="text" class="form-control" id="descripcion" name="descripcion" required>
    </div>

    <div class="text-center">
      <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar">Registrar</button>
      <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
      <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
    </div>
  </form>
  <script src="assets/js/cartel.js"></script>
  <script>
    const monto = document.getElementById('monto');
    const porcentaje = document.getElementById('porcentaje');

    monto.addEventListener('input', () => {
      // Si hay valor en monto, deshabilita porcentaje
      porcentaje.disabled = monto.value.trim() !== '';
    });

    porcentaje.addEventListener('input', () => {
      // Si hay valor en porcentaje, deshabilita monto
      monto.disabled = porcentaje.value.trim() !== '';
    });
  </script>
</body>

</html>