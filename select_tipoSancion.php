<?php
function Listar_Sancion($vConexion) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tiposancion ORDER BY nombreTipo";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idtipoSancion'];
            $Listado[$i]['NOMBRE'] = $data['nombreTipo'];
            
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
    $consulta = "SELECT * FROM estadosancion ORDER BY nombres";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['ID'] = $data['idestadoSancion'];
            $Listado[$i]['NOMBRE'] = $data['nombres'];
            
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>

<?php
function Listar_Sancion_Empleado($vConexion,$empleado) {

    $Listado=array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tiposancion, sancion, estadosancion as e, empleado WHERE sancion.idEmpleado=$empleado
    AND  sancion.IdTipoSancion= tiposancion.idtiposancion and empleado.idempleado=sancion.idEmpleado and sancion.idEstadoSancion=e.idestadoSancion  ORDER BY sancion.fecha_inicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
     $rs = mysqli_query($vConexion, $consulta);
        
     //3) el resultado deberá organizarse en una matriz, entonces lo recorro
     $i=0;
    while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['FECHAINICIO'] = $data['fecha_inicio'];
            $Listado[$i]['FECHAFIN'] = $data['fecha_fin'];
            $Listado[$i]['NOMBRE'] = $data['nombre'];
            $Listado[$i]['APELLIDO'] = $data['apellido'];
            $Listado[$i]['NOMBRETIPO'] = $data['nombreTipo'];
            $Listado[$i]['DIAS'] = $data['cantidadDias'];
            $Listado[$i]['ESTADO'] = $data['nombres'];
            $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;

}
?>