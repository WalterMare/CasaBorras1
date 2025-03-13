<?php
function Listar_EstadoSancion($vConexion) {

    $Listado = array();

    // 1) genero la consulta que deseo
    $consulta = "SELECT * FROM estadosancion ORDER BY nombres";

    // 2) a la conexión actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);
    
    // 3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idestadoSancion'];
        $Listado[$i]['NOMBRE'] = $data['nombres'];
        $i++;
    }

    // devuelvo el listado generado en el array $Listado. (Podrá salir vacío o con datos)
    return $Listado;
}
?>
