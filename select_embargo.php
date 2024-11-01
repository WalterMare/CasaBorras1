<?php
function Listar_EmbargoEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM embargo, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado=embargo.idEmpleado   ORDER BY fecha";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['ID']= $data['idempleado'];
            $Listado[$i]['FECHA'] = $data['fecha'];
            $Listado[$i]['MONTO'] = $data['monto'];
            $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
           
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>
<?php
function Listar_EmbargodeEmpleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM embargo, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado= embargo.idEmpleado   ORDER BY fecha";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    
     $data = mysqli_fetch_array($rs);
     if (!empty($data)){
            $Listado['NOMBRE'] = $data['nombre'];
            $Listado['APELLIDO'] = $data['apellido'];
            $Listado['ID']= $data['idempleado'];
            $Listado['FECHA'] = $data['fecha'];
            $Listado['MONTO'] = $data['monto'];
            $Listado['DESCRIPCION'] = $data['descripcion'];
          
        
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>

<?php
function Eliminar_Consulta($vConexion,$empleado) {

   
    //1) genero la consulta que me dice que existe el elemento que deseo eliminar
    $consulta = "SELECT idEmpleado FROM embargo WHERE idEmpleado=$empleado";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     
    
     $data = mysqli_fetch_array($rs);
     if (!empty($data['idEmpleado'])){
            
        mysqli_query($vConexion, " DELETE FROM embargo WHERE idEmpleado=$empleado");
        return true;  
        
    }else{
        return false;
    }

}
?>