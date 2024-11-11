<?php
function Listar_Licencia($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipoLicencia ORDER BY descripcion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
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
function Listar_Licencia_Bis($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipoLicencia  ORDER BY descripcion";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
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
function Listar_Estado($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM estadolicencia ORDER BY nombreEstado";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
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
function Listar_Licencia_Empleado($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipolicencia, licencia, estadolicencia as e, empleado WHERE licencia.idEmpleado=$empleado
    AND  licencia.IdTipo= tipolicencia.idtipoLicencia and empleado.idempleado=licencia.idEmpleado and licencia.idEstado=e.idestadoLicencia  ORDER BY licencia.fechainicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idlicencia'];
        $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
        $Listado[$i]['FECHAFIN'] = $data['fechafin'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['NOMBRETIPO'] = $data['descripcion'];
        $Listado[$i]['DIAS'] = $data['cantidaddias'];
        $Listado[$i]['ESTADO'] = $data['nombreEstado'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>
<?php
function Listar_Licencia_Licencia($vConexion, $licencia)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipolicencia, licencia, estadolicencia as e, empleado WHERE licencia.IdTipo=$licencia
    AND  licencia.IdTipo= tipolicencia.idtipoLicencia and empleado.idempleado=licencia.idEmpleado and licencia.idEstado=e.idestadoLicencia  ORDER BY licencia.fechainicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idlicencia'];
        $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
        $Listado[$i]['FECHAFIN'] = $data['fechafin'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['NOMBRETIPO'] = $data['descripcion'];
        $Listado[$i]['DIAS'] = $data['cantidaddias'];
        $Listado[$i]['ESTADO'] = $data['nombreEstado'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_Licencia_Empleado_Licencia($vConexion, $empleado, $licencia)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipolicencia, licencia, estadolicencia as e, empleado WHERE licencia.idEmpleado=$empleado AND licencia.IdTipo=$licencia
    AND  licencia.IdTipo= tipolicencia.idtipoLicencia and empleado.idempleado=licencia.idEmpleado and licencia.idEstado=e.idestadoLicencia  ORDER BY licencia.fechainicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idlicencia'];
        $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
        $Listado[$i]['FECHAFIN'] = $data['fechafin'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['NOMBRETIPO'] = $data['descripcion'];
        $Listado[$i]['DIAS'] = $data['cantidaddias'];
        $Listado[$i]['ESTADO'] = $data['nombreEstado'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>


<?php
function Listar_Licencias($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM tipolicencia, licencia, estadolicencia as e, empleado WHERE  licencia.IdTipo= tipolicencia.idtipoLicencia and empleado.idempleado=licencia.idEmpleado and licencia.idEstado=e.idestadoLicencia  ORDER BY licencia.fechainicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idlicencia'];
        $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
        $Listado[$i]['FECHAFIN'] = $data['fechafin'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['NOMBRETIPO'] = $data['descripcion'];
        $Listado[$i]['DIAS'] = $data['cantidaddias'];
        $Listado[$i]['ESTADO'] = $data['nombreEstado'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
//funcion que te devuelve los empleados que no tienen detalles de licencias asociados
function Listar_Detalle_Licencia_Empleado($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT 
  E.nombre, 
  E.apellido, 
  L.idlicencia, 
  L.fechainicio, 
  L.fechafin, 
  L.cantidaddias, 
  DL.Descripcion, 
  DL.Documentacion, 
  DL.FechaCreacion, 
  DL.Usuariocreacion
FROM 
  empleado E
INNER JOIN 
  licencia L ON E.idempleado = L.idEmpleado
INNER JOIN 
  detallelicencia DL ON L.idlicencia = DL.idLicencia
WHERE 
  E.idempleado = $empleado";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['IDLICENCIA'] = $data['idLicencia'];
        $Listado[$i]['DOCUMENTO'] = $data[base64_encode('Documentacion')];
        $Listado[$i]['FECHACREACION'] = $data['FechaCreacion'];
        $Listado[$i]['DESCRIPCION'] = $data['Descripcion'];
        $Listado[$i]['USUARIO'] = $data['UsuarioCreacion'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>
<?php
//funcion que te devuelve los empleados que no tienen detalles de licencias asociados
function Listar_Detalle_Licencia($vConexion, $licencia)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT 
  U.user,
  DL.Descripcion, 
  DL.FechaCreacion
FROM 
licencia AS L, detallelicencia AS DL, usuario AS U 
WHERE L.idlicencia=DL.idLicencia and DL.UsuarioCreacion=U.idusuario AND DL.idLicencia= $licencia";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $data = mysqli_fetch_array($rs);
    if (!empty($data)) {
        $Listado['FECHACREACION'] = $data['FechaCreacion'];
        $Listado['DESCRIPCION'] = $data['Descripcion'];
        $Listado['USUARIO'] = $data['user'];
        
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

