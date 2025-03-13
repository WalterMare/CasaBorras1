<?php

session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$MiConexion = ConexionBD();

// Eliminar usuario
if (isset($_GET['id'])) {
    $idusuario = intval($_GET['id']);
    
    // Verificar si el usuario existe
    $query_verificar = "SELECT * FROM usuario WHERE idusuario = ?";
    $stmt_verificar = mysqli_prepare($MiConexion, $query_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "i", $idusuario);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    
    if (mysqli_num_rows($resultado_verificar) > 0) {
        // Proceder con la eliminación
        $query_eliminar = "DELETE FROM usuario WHERE idusuario = ?";
        $stmt_eliminar = mysqli_prepare($MiConexion, $query_eliminar);
        mysqli_stmt_bind_param($stmt_eliminar, "i", $idusuario);
        
        if (mysqli_stmt_execute($stmt_eliminar)) {
            $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar el usuario.";
        }
    } else {
        $_SESSION['mensaje'] = "El usuario no existe.";
    }
    
    header("Location: Listado_Usuarios.php");
    exit;
}
?>