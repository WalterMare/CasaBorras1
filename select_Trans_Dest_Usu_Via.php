<?php
function Listar_Reporte_2($vConexion, $usuario)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $SQL = "SELECT  E.nombre, E.apellido as apellido, E.idempleado, E.estado,E.fecha_inicio,
    E.ciudad, E.tel, E.imagen, C.descripcion as nomcargo,C.idcargo,P.idprovincia,P.nombre as nomprov
    FROM empleado E, cargo C,provincia P
    WHERE  C.idcargo = E.Idcargo AND E.idprovincia = P.idprovincia 
    order by apellido";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $SQL);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['ESTADO'] = $data['estado'];
        $Listado[$i]['FECHAINICIO'] = date("d/m/Y", strtotime($data['fecha_inicio']));;
        $Listado[$i]['CIUDAD'] = $data['ciudad'];
        $Listado[$i]['TEL'] = $data['tel'];
        $Listado[$i]['IMAGEN'] = $data['imagen'];
        $Listado[$i]['CARGO'] = $data['nomcargo'];
        $Listado[$i]['PROVINCIA'] = $data['nomprov'];
        $Listado[$i]['ID']=$data['idempleado'];
        $i++;
    };
    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
