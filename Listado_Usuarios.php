<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$MiConexion = ConexionBD();

// Obtener la lista de usuarios
$query = "SELECT u.idusuario, u.user, t.descripcion FROM usuario u INNER JOIN tipo t ON u.Idtipo = t.idtipo ORDER BY t.descripcion";
$resultado = mysqli_query($MiConexion, $query);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Lista de Usuarios</title>
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

    <h5 class="card-title">Datos de usuarios del Sistema</h5>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($usuario = mysqli_fetch_assoc($resultado)) { ?>
                <tr>
                    <td><?php echo $usuario['idusuario']; ?></td>
                    <td><?php echo $usuario['user']; ?></td>
                    <td><?php echo $usuario['descripcion']; ?></td>
                    <td>
                        <a href="Modificar_Usuario.php?id=<?php echo $usuario['idusuario']; ?>" class="badge bg-info text-dark">Modificar</a>
                        <a href="Eliminar_Usuario.php?id=<?php echo $usuario['idusuario']; ?>" class="badge bg-danger text-light" onclick="return confirm('¿Seguro que desea eliminar este usuario?');">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>

</body>

</html>