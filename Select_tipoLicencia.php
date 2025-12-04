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

function Listar_Licencia_Empleado_preliquidacion($vConexion, $empleado, $idPreliquidacion)
{
    $Listado = array();
    $empleado = (int)$empleado;
    $idPreliquidacion = (int)$idPreliquidacion;

    // Obtener el período desde la preliquidación
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($vConexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $resultadoPeriodo = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($resultadoPeriodo);

    if (!$filaPeriodo) return $Listado;

    list($fechaInicioPeriodo, $fechaFinPeriodo) = array_map('trim', explode(' a ', $filaPeriodo['periodo']));

    // Traer licencias que se solapan
    $consulta = "
        SELECT 
            l.idlicencia,
            l.fechainicio,
            l.fechafin,
            l.cantidaddias,
            tl.descripcion,
            e.nombreEstado,
            emp.nombre,
            emp.apellido
        FROM licencia l
        JOIN tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
        JOIN estadolicencia e ON l.idEstado = e.idestadoLicencia
        JOIN empleado emp ON l.idEmpleado = emp.idempleado
        WHERE l.idEmpleado = ?
          AND l.fechainicio <= ?
          AND l.fechafin >= ?
          AND l.idEstado IN (1, 3)
        ORDER BY l.fechainicio
    ";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "iss", $empleado, $fechaFinPeriodo, $fechaInicioPeriodo);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($l = mysqli_fetch_assoc($rs)) {

        $inicioLic = $l['fechainicio'];
        $finLic = $l['fechafin'];

        // calcular solapamiento real
        $inicioAplicable = max($inicioLic, $fechaInicioPeriodo);
        $finAplicable = min($finLic, $fechaFinPeriodo);

        $diasEnPeriodo = 0;

        if ($inicioAplicable <= $finAplicable) {
            $diasEnPeriodo = (strtotime($finAplicable) - strtotime($inicioAplicable)) / 86400 + 1;
        }

        $Listado[] = [
            'ID'           => $l['idlicencia'],
            'FECHAINICIO'  => $l['fechainicio'],
            'FECHAFIN'     => $l['fechafin'],
            'NOMBRE'       => $l['nombre'],
            'APELLIDO'     => $l['apellido'],
            'NOMBRETIPO'   => $l['descripcion'],
            'DIAS_TOTALES' => $l['cantidaddias'],   // por si lo necesitás
            'DIAS_PERIODO' => $diasEnPeriodo,       // ESTE ES EL IMPORTANTE
            'ESTADO'       => $l['nombreEstado'],
        ];
    }

    return $Listado;
}


/*function Listar_Licencia_Empleado_preliquidacion($vConexion, $empleado, $idPreliquidacion)
{
    $Listado = array();
    $empleado = (int)$empleado;
    $idPreliquidacion = (int)$idPreliquidacion;

    // Obtener el período desde la tabla preliquidacion
    $consultaPeriodo = "SELECT periodo FROM preliquidacion WHERE idpreliquidacion = ?";
    $stmtPeriodo = mysqli_prepare($vConexion, $consultaPeriodo);
    mysqli_stmt_bind_param($stmtPeriodo, "i", $idPreliquidacion);
    mysqli_stmt_execute($stmtPeriodo);
    $resultadoPeriodo = mysqli_stmt_get_result($stmtPeriodo);
    $filaPeriodo = mysqli_fetch_assoc($resultadoPeriodo);

    if (!$filaPeriodo) {
        return $Listado; // no encontró preliquidación
    }

    // Separar el rango "YYYY-MM-DD a YYYY-MM-DD"
    $partes = explode(' a ', $filaPeriodo['periodo']);
    if (count($partes) !== 2) {
        return $Listado; // formato inválido
    }

    $fechaInicioPeriodo = $partes[0];
    $fechaFinPeriodo = $partes[1];

    // Consulta con JOIN y filtro por período
    $consulta = "
        SELECT 
            l.idlicencia,
            l.fechainicio,
            l.fechafin,
            l.cantidaddias,
            tl.descripcion,
            e.nombreEstado,
            emp.nombre,
            emp.apellido
        FROM licencia l
        JOIN tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
        JOIN estadolicencia e ON l.idEstado = e.idestadoLicencia
        JOIN empleado emp ON l.idEmpleado = emp.idempleado
        WHERE l.idEmpleado = ?
          AND l.fechainicio <= ?
          AND l.fechafin >= ?
        ORDER BY l.fechainicio
    ";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "iss", $empleado, $fechaFinPeriodo, $fechaInicioPeriodo);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[] = [
            'ID' => $data['idlicencia'],
            'FECHAINICIO' => $data['fechainicio'],
            'FECHAFIN' => $data['fechafin'],
            'NOMBRE' => $data['nombre'],
            'APELLIDO' => $data['apellido'],
            'NOMBRETIPO' => $data['descripcion'],
            'DIAS' => $data['cantidaddias'],
            'ESTADO' => $data['nombreEstado'],
        ];
    }

    return $Listado;
}*/


function Clasificar_Dias_Licencias($licencias)
{
    // Licencias pagas según tu configuración
    $licenciasPagas = ['Maternidad', 'Paternidad', 'Fallecimiento', 'Matrimonio', 'Examen', 'Carga Pública', 'Enfermedad'];
    if (!is_array($licencias)) {
        $licencias = [];
    }


    $diasPagos = 0;
    $diasNoPagos = 0;
    $estadosValidos = ['Aprobada', 'Finalizada'];

    foreach ($licencias as $lic) {

        // Validar que existan las claves
        if (empty($lic['DIAS_PERIODO']) || empty($lic['NOMBRETIPO']) || empty($lic['ESTADO'])) continue;

        
        // SOLO licencias aprobadas deben considerarse
       if (!in_array($lic['ESTADO'], $estadosValidos)) continue;

        $tipo = $lic['NOMBRETIPO'];
        $dias = (int)$lic['DIAS_PERIODO'];

        if (in_array($tipo, $licenciasPagas)) {
            // Estas licencias son pagas → NO descuentan del sueldo
            $diasPagos += $dias;
        } else {
            // Cualquier otra licencia (incluye "Reserva de Puesto") es NO paga
            $diasNoPagos += $dias;
        }
    }

    return [
        'dias_pagos' => $diasPagos,
        'dias_no_pagos' => $diasNoPagos
    ];
}




?>

