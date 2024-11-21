<?php
function Listar_Reporte_Empleado($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM reporte WHERE reporte.idEmpleado=$empleado ORDER BY reporte.fecha";
    

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idreporte'];
        $Listado[$i]['FECHA'] = $data['fecha'];
        $Listado[$i]['IDREPORTE'] = $data['idTipoReporte'];
        $Listado[$i]['IDEMPLEADO'] = $data['idEmpleado'];
        $Listado[$i]['PERIODOINICIO'] = $data['periodoInicio'];
        $Listado[$i]['PERIODOFIN'] = $data['periodoFin'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_Reporte_Empleado2($vConexion, $empleado,$tipo)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM reporte WHERE reporte.idEmpleado=$empleado and reporte.idTipoReporte=$tipo  ORDER BY reporte.fecha";
    

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idreporte'];
        $Listado[$i]['FECHA'] = $data['fecha'];
        $Listado[$i]['IDREPORTE'] = $data['idTipoReporte'];
        $Listado[$i]['IDEMPLEADO'] = $data['idEmpleado'];
        $Listado[$i]['PERIODOINICIO'] = $data['periodoInicio'];
        $Listado[$i]['PERIODOFIN'] = $data['periodoFin'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_Reporte_Empleado3($vConexion, $empleado,$tipo)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT l.idlicencia, l.fechainicio, l.fechafin, l.cantidaddias, t.descripcion AS tipo_licencia, e.nombreEstado
    FROM licencia l
    JOIN tipolicencia t ON l.IdTipo = t.idtipoLicencia
    JOIN estadolicencia e ON l.IdEstado = e.idestadoLicencia
    WHERE l.idEmpleado = $empleado";
    

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idlicencia'];
        $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
        $Listado[$i]['FECHAFIN'] = $data['fechafin'];
        $Listado[$i]['CANTIDADDIAS'] = $data['cantidaddias'];
        $Listado[$i]['TIPO'] = $data['tipo_licencia'];
        $Listado[$i]['ESTADO'] = $data['nombreEstado'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_Reporte_Empleado3_Detalle($vConexion, $idLicencia)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT dl.descripcion, dl.documentacion, dl.fechacreacion, e.nombre, e.apellido
          FROM detallelicencia dl
          JOIN usuario u ON dl.usuarioCreacion = u.idusuario
          JOIN empleado e ON u.IdEmpleado= e.idempleado
          WHERE dl.idLicencia = $idLicencia";
    

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    if (!empty($rs)) { 
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
        $Listado[$i]['FECHACREACION'] = $data['fechacreacion'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['DOCUMENTACION'] = "<a href='data:application/octet-stream;base64," . base64_encode($data['documentacion']) . "' download='documentacion_licencia'>Descargar Documentación</a>";
     
        $i++;
    }
    }else{
        echo "No hay detalles disponibles para esta licencia.";
    }

    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_Reporte_Empleado_Embargo($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT 
    e.idembargo, 
    e.fecha, 
    e.monto, 
    e.descripcion
FROM 
    embargo e
WHERE 
    e.idEmpleado = $empleado";
    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idembargo'];
        $Listado[$i]['FECHA'] = $data['fecha'];
        $Listado[$i]['MONTO'] = $data['monto'];
        $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
   
        $i++;
    }
    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>