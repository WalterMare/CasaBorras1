<?php
function Listar_Estadocivil($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM estadocivil ORDER BY descripcion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idestadocivil'];
            $Listado[$i]['NOMBRE'] = $data['descripcion'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>