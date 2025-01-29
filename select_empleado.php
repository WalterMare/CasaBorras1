<?php
function Listar_empleado($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM empleado WHERE empleado.fecha_baja IS NULL ORDER BY apellido";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idempleado'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['IMAGEN'] = $data['imagen'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_Imagen_empleado($vConexion,$empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT imagen FROM empleado WHERE idempleado=$empleado";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $data = mysqli_fetch_array($rs);
    if ($data) {
        $Listado[$i]['IMAGEN'] = $data['imagen'];
    
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_empleado_activos($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM empleado WHERE empleado.estado=1 ";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idempleado'];
       
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_empleado_activos_bis($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM empleado WHERE empleado.estado=1 ";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idempleado'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_empleado_inactivos($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM empleado WHERE empleado.estado=0  and empleado.fecha_baja IS NULL";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idempleado'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_empleado_sin_ObraSocial($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM empleado WHERE idEmpleado NOT IN (SELECT idEmpleado FROM obrasocial )";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idempleado'];
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

<?php
function Listar_empleadoId($vConexion,$empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT 
    e.idempleado,
    e.nombre,
    e.apellido,
    CASE 
        WHEN e.estado = 1 THEN 'Activo'
        ELSE 'Inactivo'
    END AS estado_empleado,
    e.fecha_inicio,
    c.descripcion AS cargo,
    e.ciudad,
    e.tel,
    p.nombre AS provincia,
    e.dni,
    s.tipo AS sexo
FROM 
    empleado e
JOIN 
    cargo c ON e.Idcargo = c.idcargo
JOIN 
    estadocivil ec ON e.IdestadoCivil = ec.idestadocivil
JOIN 
    sexo s ON e.Idsexo = s.idsexo
JOIN 
    provincia p ON e.idprovincia = p.idprovincia
WHERE 
    e.idempleado = $empleado";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    if ($data = mysqli_fetch_array($rs)) {
        $Listado['NOMBRE'] = $data['nombre'];
        $Listado['APELLIDO'] = $data['apellido'];
        $Listado['ESTADO'] = $data['estado_empleado'];  // Usamos 'estado_empleado' como en la consulta
        $Listado['FECHAINICIO'] = $data['fecha_inicio'];
        $Listado['CARGO'] = $data['cargo'];
        $Listado['CIUDAD'] = $data['ciudad'];
        $Listado['PROVINCIA'] = $data['provincia'];
        $Listado['DNI'] = $data['dni'];
        $Listado['SEXO'] = $data['sexo'];
        $Listado['ID']=$data['idempleado'];
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>

