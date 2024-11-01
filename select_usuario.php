<?php
function Listar_Usuario($vConexion) {

    $Listado=array();

    //1) genero la consulta para que cargue todos los choferes con estado activo
    $consulta = "SELECT * FROM usuarios WHERE Estado=1  AND idNivel=3  ORDER BY Apellido ";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['id'];
            $Listado[$i]['Apellido'] = $data['Apellido'];
            $Listado[$i]['Nombre'] = $data['Nombre'];
            $Listado[$i]['DNI'] = $data['DNI'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>