<?php
function Listar_roles_funcionales($conexion) {
    $sql = "SELECT idrol_funcional AS ID, nombre AS NOMBRE FROM rol_funcional";
    $result = mysqli_query($conexion, $sql);
    $roles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $roles[] = $row;
    }
    return $roles;
}
?>
