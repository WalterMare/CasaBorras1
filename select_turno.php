<?php
function Listar_Turno($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM turno ORDER BY idturno";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idturno'];
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['HORA_INICIO']= $data['hora_inicio'];
            $Listado[$i]['HORA_FIN']= $data['hora_fin'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>