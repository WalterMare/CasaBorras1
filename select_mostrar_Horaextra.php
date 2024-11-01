<?php
function Listar_horaExtradeEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM horaextra, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado=horaextra.IdEmpleado   ORDER BY fecha";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    
     $data = mysqli_fetch_array($rs);
     if (!empty($data)){
            $Listado['NOMBRE'] = $data['nombre'];
            $Listado['APELLIDO'] = $data['apellido'];
            $Listado['ID']= $data['idempleado'];
            $Listado['FECHA'] = $data['fecha'];
            $Listado['HORAS'] = $data['cantidadHoras'];
        
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>