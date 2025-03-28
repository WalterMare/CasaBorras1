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


require_once 'validar_datos_anticipo.php';
require_once 'insertar_Anticipo.php';


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
      if (InsertarAnticipo($conexion) != false) {
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


  <form class="row g-3" method="post"> <!--se agrego el metodo post para la captura de datos -->
    <div class="col-6">
      <label name="selector" for="selector" class="form-label">Empleado al que se asignará(*)</label>
      <select class="form-select" aria-label="Selector" id="selector" name="empleado"> <!--combobox ya cargado con las marcas traidas desde la bd -->
        <option value="">Selecciona una opción</option>
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
      <label for="fecha" class="form-label">Fecha Otorgamiento (*)</label>
      <input type="date" class="form-control" id="fecha" name="fecha">
    </div>

    <div class="col-6">
      <label for="monto" class="form-label">Monto(*)</label>
      <input type="number" step="0.01" class="form-control" id="monto" name='monto'>
    </div>

    <div class="col-6">
      <label for="otorgante" class="form-label">Persona que Otorga(*)</label>
      <input type="text" class="form-control" id="otorgante" name='otorgante'>
    </div>



    <div class="text-center">
      <button class="btn btn-primary" type="submit" value="Registrar" name="BotonRegistrar">Registrar</button>
      <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
      <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
    </div>
  </form><!-- Vertical Form -->
 
</body>

</html>