<?php
function Obtener_roles_usuario($conexion, $idUsuario) {
    $sql = "SELECT idrol_funcional FROM usuario_rol_funcional WHERE idusuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row['idrol_funcional'];
    }
    return $roles;
}
?>
