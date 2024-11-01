<?php
function Listar_Transporte($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT transporte.id,Denominacion, Modelo, Patente FROM marcas JOIN transporte ON marcas.id=transporte.IdMarca ORDER BY Denominacion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['id'];
            $Listado[$i]['Denominacion'] = $data['Denominacion'];
            $Listado[$i]['Modelo'] = $data['Modelo'];
            $Listado[$i]['Patente'] = $data['Patente'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>