<?php
function Listar_ObraSocialEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM obrasocial, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado=obrasocial.idEmpleado   ORDER BY nombre";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['ID']= $data['idempleado'];
            $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>
<?php
function Listar_ObraSocialdeEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM obrasocial, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado=obrasocial.idEmpleado   ORDER BY nombre";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    
     $data = mysqli_fetch_array($rs);
     if (!empty($data)){
            $Listado['NOMBRE'] = $data['nombre'];
            $Listado['APELLIDO'] = $data['apellido'];
            $Listado['ID']= $data['idempleado'];
            $Listado['DESCRIPCION'] = $data['descripcion'];
          
        
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>