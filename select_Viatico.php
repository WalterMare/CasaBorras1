<?php
function Listar_tipo_viatico($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipo_viatico ORDER BY descripcion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idTipo_viatico'];
            $Listado[$i]['NOMBRE'] = $data['descripcion'];
            
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>

<?php
function Listar_viaticoEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipo_viatico, viatico, empleado WHERE viatico.idEmpleado=$empleado
    AND  viatico.idTipo= tipo_viatico.idTipo_viatico and empleado.idempleado=viatico.idEmpleado   ORDER BY fechaotorgamiento";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
            $Listado[$i]['FECHA'] = $data['fechaotorgamiento'];
            $Listado[$i]['MONTO'] = $data['monto'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>