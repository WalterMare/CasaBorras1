
<?php
function Listar_horaExtraEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM horaextra, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado=horaextra.IdEmpleado   ORDER BY fecha";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['ID']= $data['idempleado'];
            $Listado[$i]['FECHA'] = $data['fecha'];
            $Listado[$i]['HORAS'] = $data['cantidadHoras'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>