<?php

session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}

//voy a necesitar la conexion: incluyo la funcion de Conexion.
require_once 'conexiondb.php';

//genero una variable para usar mi conexion desde donde me haga falta
//no envio parametros porque ya los tiene definidos por defecto
$MiConexion = ConexionBD();

require_once 'select_Trans_Dest_Usu_Via.php';
// Obtener el filtro del estado desde el formulario (si está definido)
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : 'todos';
$filtro_documento = isset($_GET['filtro_documento']) ? trim($_GET['filtro_documento']) : '';


// Llamar a la función con el filtro seleccionado
$ListadoReporte = Listar_Reporte_2($MiConexion, $_SESSION['Usuario_Nivel'], $filtro_estado, $filtro_documento);
$CantidadReportes = count($ListadoReporte);

// Paginación
$porPagina = 10; // registros por página
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$totalRegistros = count($ListadoReporte);
$totalPaginas = ceil($totalRegistros / $porPagina);
$inicio = ($paginaActual - 1) * $porPagina;

// Cortar array para mostrar solo los registros de esta página
$ListadoReportePagina = array_slice($ListadoReporte, $inicio, $porPagina);

//se incluye la funcion para calcular las fechas asi obtener el color que le corresponde a la tabla
//require_once 'testFechas.php';

?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Lista Empleados</title>
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
 
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</head>

<body>

  <!-- ======= Header ======= -->
  <?php include_once 'partes/header.php' ?>
  <!-- End Header -->
  <!-- ======= Sidebar ======= -->
  <?php include_once 'partes/menu.php'; ?>
  <!-- End Sidebar-->
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Lista empleados registrados</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item">Gestor de personal</li>
          <li class="breadcrumb-item active">Listado de empleados</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <!-- Agregado del filtro activo-Inactivo-De Baja -->
              <form method="GET" action="listado_empleados.php" class="mb-4">
                <div class="row">
                  <div class="col-md-4">
                    <label for="filtro_estado" class="form-label">Filtrar por estado:</label>
                    <select name="filtro_estado" id="filtro_estado" class="form-select">
                      <option value="todos" <?php echo (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                      <option value="activos" <?php echo (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == 'activos') ? 'selected' : ''; ?>>Activos</option>
                      <option value="inactivos" <?php echo (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == 'inactivos') ? 'selected' : ''; ?>>Inactivos (Licencias/Vacaciones)</option>
                      <option value="baja" <?php echo (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == 'baja') ? 'selected' : ''; ?>>Dados de baja</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="filtro_documento" class="form-label">Filtrar por documento:</label>
                    <input type="number" name="filtro_documento" id="filtro_documento" class="form-control"
                      value="<?php echo isset($_GET['filtro_documento']) ? htmlspecialchars($_GET['filtro_documento']) : ''; ?>"
                      min="0" step="1" pattern="\d*">
                  </div>
                  <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                  </div>
                </div>
              </form>

              <h5 class="card-title">Empleados cargados</h5>


              <!-- Default Table -->
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Id</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Ciudad</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Fecha Inicio</th>
                    <th scope="col">Fecha de Baja</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $indice = $inicio + 1;
                  foreach ($ListadoReportePagina as $item) {
                    $estaDadoDeBaja = !empty($item['FECHABAJA']) && $item['FECHABAJA'] != 'N/A';
                  ?>
                    <tr class="<?php echo $estaDadoDeBaja ? 'table-info' : ''; ?>">
                      <th scope="row"><?php echo $indice++; ?></th>
                      <td><?php echo $item['ID']; ?></td>
                      <td><?php echo $item['NOMBRE']; ?></td>
                      <td><?php echo $item['APELLIDO']; ?></td>
                      <td><?php echo $item['DOCUMENTO']; ?></td>
                      <td><?php echo $item['CIUDAD']; ?></td>
                      <td><?php echo $item['PROVINCIA']; ?></td>
                      <td><?php echo $item['FECHAINICIO']; ?></td>
                      <td><?php echo $item['FECHABAJA']; ?></td>
                      <td>
                        <?php if (!$estaDadoDeBaja) { ?>
                          <a onclick="return confirm('Está seguro que desea cambiar el Estado?');"
                            href="cambiar_estado.php?ESTADO=<?php echo $item['ESTADO']; ?>&ID=<?php echo $item['ID']; ?>" title="Modificar estado">
                            <span class="badge bg-<?php echo $item['ESTADO'] == 1 ? 'success' : 'danger'; ?>">
                              <i class="bi bi-check-circle me-1"></i>
                            </span>
                          </a>
                        <?php } ?>
                      </td>
                      <td><?php echo $item['CARGO']; ?></td>
                      <td>
                        <a href="Mostrar_datos.php?ID=<?php echo $item['ID']; ?>" title="Ver">
                          <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i></span>
                        </a>
                        <a href="Modificar_datos.php?ID=<?php echo $item['ID']; ?>" title="Modificar">
                          <span class="badge bg-info text-dark"><i class="bi bi-info-circle me-1"></i></span>
                        </a>
                        <?php if (!$estaDadoDeBaja) { ?>
                          <a href="#" data-bs-toggle="modal" data-bs-target="#modalBaja<?php echo $item['ID']; ?>" title="Dar de baja">
                            <span class="badge bg-danger text-light"><i class="bi bi-x-circle me-1"></i></span>
                          </a>
                        <?php } ?>
                      </td>
                    </tr>
                    <div class="modal fade" id="modalBaja<?php echo $item['ID']; ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <form action="dar_baja_empleado.php" method="post">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Dar de baja al empleado</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="id_empleado" value="<?php echo $item['ID']; ?>">
                              <label for="motivo_baja">Seleccione el motivo:</label>
                              <select name="motivo_baja" class="form-select" required>
                                <option value="">Seleccione un motivo</option>
                                <option value="Renuncia voluntaria">Renuncia voluntaria</option>
                                <option value="Sanción grave">Sanción grave</option>
                                <option value="Fallecimiento">Fallecimiento</option>
                                <option value="Jubilación">Jubilación</option>
                                <option value="Otro">Otro</option>
                              </select>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-danger">Confirmar baja</button>
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>

                  <?php } ?>
                </tbody>

              </table>
              <!-- End Default Table Example -->
              <nav>
                <ul class="pagination justify-content-center">
                  <?php if ($paginaActual > 1) { ?>
                    <li class="page-item">
                      <a class="page-link" href="?pagina=<?php echo $paginaActual - 1; ?>&filtro_estado=<?php echo $filtro_estado; ?>">Anterior</a>
                    </li>
                  <?php } ?>

                  <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
                    <li class="page-item <?php echo ($i == $paginaActual) ? 'active' : ''; ?>">
                      <a class="page-link" href="?pagina=<?php echo $i; ?>&filtro_estado=<?php echo $filtro_estado; ?>"><?php echo $i; ?></a>
                    </li>
                  <?php } ?>

                  <?php if ($paginaActual < $totalPaginas) { ?>
                    <li class="page-item">
                      <a class="page-link" href="?pagina=<?php echo $paginaActual + 1; ?>&filtro_estado=<?php echo $filtro_estado; ?>">Siguiente</a>
                    </li>
                  <?php } ?>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->


  <!-- ======= Footer ======= -->
  <?php include_once 'partes/footer.php' ?>
  <!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>