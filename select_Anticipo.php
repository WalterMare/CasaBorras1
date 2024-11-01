<?php
function Listar_anticipoEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM anticipo, empleado WHERE anticipo.IdEmpleado=$empleado
    and empleado.idempleado=anticipo.IdEmpleado ORDER BY fechaOtorgamiento";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO']= $data['apellido'];
            $Listado[$i]['FECHA'] = $data['fechaOtorgamiento'];
            $Listado[$i]['MONTO'] = $data['monto'];
            $Listado[$i]['OTORGANTE'] = $data['usuario'];
          
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>