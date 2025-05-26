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

    $Listado = array();

    $empleado = (int)$empleado; // proteger el valor

    // Consulta clara con alias
    $consulta = "
        SELECT 
            s.fecha_inicio AS fecha_inicio_sancion,
            s.fecha_fin AS fecha_fin_sancion,
            s.cantidadDias,
            s.idsancion,
            t.nombreTipo,
            e.nombres AS estado_nombre,
            emp.nombre AS nombre_empleado,
            emp.apellido AS apellido_empleado
        FROM sancion s
        JOIN tiposancion t ON s.IdTipoSancion = t.idtiposancion
        JOIN estadosancion e ON s.idEstadoSancion = e.idestadoSancion
        JOIN empleado emp ON s.idEmpleado = emp.idempleado
        WHERE s.idEmpleado = $empleado
        ORDER BY s.fecha_inicio
    ";

    $rs = mysqli_query($vConexion, $consulta);

    if (!$rs) {
        echo "Error en la consulta: " . mysqli_error($vConexion);
        return $Listado;
    }

    $i = 0;
    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[$i]['FECHAINICIO'] = $data['fecha_inicio_sancion'];
        $Listado[$i]['FECHAFIN'] = $data['fecha_fin_sancion'];
        $Listado[$i]['NOMBRE'] = $data['nombre_empleado'];
        $Listado[$i]['APELLIDO'] = $data['apellido_empleado'];
        $Listado[$i]['NOMBRETIPO'] = $data['nombreTipo'];
        $Listado[$i]['DIAS'] = $data['cantidadDias'];
        $Listado[$i]['ESTADO'] = $data['estado_nombre'];
        $Listado[$i]['IDSANCION'] = $data['idsancion'];
        $i++;
    }

    return $Listado;

}
?>