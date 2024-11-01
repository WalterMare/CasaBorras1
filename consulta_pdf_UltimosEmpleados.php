<?php
function Listar_ultimosEmpleados2($vConexion,$tiempo) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT E.nombre, E.apellido, E.fecha_inicio, E.estado, C.descripcion FROM empleado AS E, cargo AS C WHERE E.fecha_inicio>= DATE_SUB(CURDATE(), INTERVAL $tiempo YEAR) AND E.Idcargo=C.idcargo ORDER BY E.fecha_inicio ";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['FECHA_INICIO'] = date("d/m/Y", strtotime($data['fecha_inicio']));
            $Listado[$i]['ESTADO'] = $data['estado'];
            $Listado[$i]['CARGO'] = $data['descripcion'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>