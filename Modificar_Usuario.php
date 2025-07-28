<?php
ob_start(); // Inicia el almacenamiento en búfer de salida
session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$MiConexion = ConexionBD();

require_once 'validar_registro_usuario.php';
require_once 'select_roles_funcionales.php';
require_once 'select_roles_usuario.php';

$rolesDisponibles = Listar_roles_funcionales($MiConexion); // trae todos los roles funcionales
$rolesDelUsuario = Obtener_roles_usuario($MiConexion, $_GET['id']); // trae solo los idrol_funcional asignados al usuario actual

require_once 'select_tipo.php';
$Listartipos = Listar_tipo($MiConexion);
$CantidadTipo = count($Listartipos);

$listarUsuarios = Listar_usuario($MiConexion, $_GET['id']);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Modificar Usuario</title>
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

    <!-- End Sidebar-->
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Usuarios</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de empleados</li>
                    <li class="breadcrumb-item active">Modificar Usuario</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

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

                            <?php
                            $Mensaje = '';
                            $Estilo = 'warning';
                            if (!empty($_POST['BotonModificar'])) {
                                // Estoy en condiciones de poder validar los datos
                                $Mensaje = Validar_Datos_Usuario_bis($_POST['user'], $_POST['Idtipo'], $_POST['roles'] ?? []);
                                if (empty($Mensaje)) {
                                    require_once 'Actualizar__Usuario.php';
                                    if (Modificar_usuario($MiConexion, $_GET['id']) != false) {
                                        // Mensaje de éxito
                                        $Mensaje = 'Se ha actualizado correctamente.';
                                        $_POST = array();
                                        $Estilo = 'success';

                                        // Mostrar el mensaje de éxito
                                        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                                <i class='bi bi-check-circle me-1'></i>
                                                $Mensaje
                                              </div>";

                                        // Redirigir a Listado_Usuarios.php después de 2 segundos (o puedes usar 0 para redirección inmediata)
                                        header("refresh:2;url=Usuarios.php");
                                        exit; // Termina el script para que no siga ejecutándose
                                    } else {
                                        // Si no se puede actualizar el usuario, mostrar mensaje de error
                                        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                                <i class='bi bi-exclamation-triangle me-1'></i>
                                                Error al intentar actualizar el usuario.
                                              </div>";
                                    }
                                } else {
                                    // Si hay un error con los datos
                                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                            <i class='bi bi-exclamation-triangle me-1'></i>
                                            $Mensaje
                                          </div>";
                                }
                            }
                            ob_end_flush(); ?>

                            <form class="row g-3" method="post">
                                <div class="mb-3">
                                    <label for="user" class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="user" name="user" value="<?php echo $listarUsuarios['USUARIO']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="selector" class="form-label">Tipo</label>
                                    <select class="form-select" aria-label="Selector" id="selector" name="Idtipo" required>
                                        <?php
                                        foreach ($Listartipos as $tipo) {
                                            $selected = ($tipo['ID'] == $listarUsuarios['IDTIPO']) ? 'selected' : '';
                                            echo "<option value='{$tipo['ID']}' $selected>{$tipo['NOMBRE']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Roles funcionales</label><br>
                                    <?php foreach ($rolesDisponibles as $rol): ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="roles[]" id="rol_<?php echo $rol['ID']; ?>" value="<?php echo $rol['ID']; ?>"
                                                <?php echo in_array($rol['ID'], $rolesDelUsuario) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="rol_<?php echo $rol['ID']; ?>">
                                                <?php echo $rol['NOMBRE']; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="text-center">
                                    <button class="btn btn-primary" type="submit" value="Registrar" name="BotonModificar">Modificar</button>
                                    <a href="Usuarios.php" class="text-primary fw-bold">Volver al panel</a>
                                </div>
                            </form>
                        </div>
    </main>
    <?php include_once 'partes/footer.php'; ?>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <!--<script src="assets/vendor/php-email-form/validate.js"></script> -->
    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cartel.js"></script>
</body>

</html>