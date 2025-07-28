<?php
function Listar_tipo($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipo ORDER BY descripcion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idtipo'];
            $Listado[$i]['NOMBRE'] = $data['descripcion'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}


function Listar_usuario($vConexion, $idusuario) {
    $Listado = array();

    // Consulta para obtener un solo usuario con su tipo y su IDTIPO
    $consulta = "SELECT u.idusuario, u.user, u.Idtipo AS IDTIPO, t.descripcion AS tipo 
                 FROM usuario u
                 JOIN tipo t ON u.Idtipo = t.idtipo
                 WHERE u.idusuario = ?";

    // Preparar la sentencia
    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $idusuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    // Obtener el usuario
    if ($data = mysqli_fetch_array($resultado)) {
        $Listado['ID'] = $data['idusuario'];
        $Listado['USUARIO'] = $data['user'];
        $Listado['TIPO'] = $data['tipo'];
        $Listado['IDTIPO'] = $data['IDTIPO']; // ⚠️ Esto es lo nuevo e importante
    }

    return $Listado;
}








