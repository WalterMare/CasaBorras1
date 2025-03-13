<?php

session_start();

// Verificar si el usuario está autenticado
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$MiConexion = ConexionBD();

// Eliminar sanción
if (isset($_GET['id'])) {
    $idSancion = intval($_GET['id']);

    // Verificar si la sanción existe
    $query_verificar = "SELECT * FROM sancion WHERE idsancion = ?";
    $stmt_verificar = mysqli_prepare($MiConexion, $query_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "i", $idSancion);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);

    if (mysqli_num_rows($resultado_verificar) > 0) {
        // Proceder con la eliminación
        $query_eliminar = "DELETE FROM sancion WHERE idsancion = ?";
        $stmt_eliminar = mysqli_prepare($MiConexion, $query_eliminar);
        mysqli_stmt_bind_param($stmt_eliminar, "i", $idSancion);

        if (mysqli_stmt_execute($stmt_eliminar)) {
            $_SESSION['mensaje'] = "Sanción eliminada correctamente.";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar la sanción.";
        }
    } else {
        $_SESSION['mensaje'] = "La sanción no existe.";
    }

    // Redireccionar a la lista de sanciones
    header("Location: Listar_sanciones_empleado.php");
    exit;
}

?>
