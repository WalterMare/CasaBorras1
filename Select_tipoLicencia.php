<?php
function Listar_Licencia($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipoLicencia ORDER BY descripcion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idtipoLicencia'];
            $Listado[$i]['NOMBRE'] = $data['descripcion'];
            
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>

<?php
function Listar_Estado($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM estadolicencia ORDER BY nombreEstado";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idestadoLicencia'];
            $Listado[$i]['NOMBRE'] = $data['nombreEstado'];
            
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>

<?php
function Listar_Licencia_Empleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipolicencia, licencia, estadolicencia as e, empleado WHERE licencia.idEmpleado=$empleado
    AND  licencia.IdTipo= tipolicencia.idtipoLicencia and empleado.idempleado=licencia.idEmpleado and licencia.idEstado=e.idestadoLicencia  ORDER BY licencia.fechainicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idlicencia'];
            $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
            $Listado[$i]['FECHAFIN'] = $data['fechafin'];
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['NOMBRETIPO'] = $data['descripcion'];
            $Listado[$i]['DESCRIPCIONLICENCIA'] = $data['descripciones'];
            $Listado[$i]['DIAS'] = $data['cantidaddias'];
            $Listado[$i]['ESTADO'] = $data['nombreEstado'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>