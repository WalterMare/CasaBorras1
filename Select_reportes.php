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
function Listar_Reporte_Empleado2($vConexion, $empleado, $tipo)
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
function Listar_Reporte_Empleado3($vConexion, $empleado, $tipo)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT l.idlicencia, l.fechainicio, l.fechafin, l.cantidaddias, 
       t.descripcion AS tipo_licencia, e.nombreEstado
FROM licencia l
JOIN tipolicencia t ON l.IdTipo = t.idtipoLicencia
JOIN estadolicencia e ON l.IdEstado = e.idestadoLicencia
JOIN detallelicencia d ON l.idlicencia = d.idLicencia
WHERE l.idEmpleado = $empleado
GROUP BY l.idlicencia";


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

    //1) Genero la consulta que deseo
    $consulta = "SELECT 
    dl.descripcion,
    d.Documentacion,
    d.FechaCreacion AS fecha_documento,
    dl.FechaCreacion AS fecha_detalle,
    e.nombre AS empleado_nombre,
    e.apellido AS empleado_apellido,
    tl.descripcion AS tipo_licencia,
    el.nombreEstado AS estado_licencia,
    u.user,
    dl.iddetalleLicencia  -- Asegúrate de tomarlo de la tabla detallelicencia
FROM 
    detallelicencia dl
LEFT JOIN 
    documento d ON dl.iddetalleLicencia = d.iddetalleLicencia
JOIN 
    licencia l ON dl.idLicencia = l.idlicencia
JOIN 
    empleado e ON l.idEmpleado = e.idempleado
JOIN 
    tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
JOIN 
    estadolicencia el ON l.IdEstado = el.idestadoLicencia
JOIN 
    usuario u ON dl.idUsuario = u.idusuario         
WHERE 
    dl.idLicencia = $idLicencia";


    //2) Ejecuto la consulta y obtengo el resultado
    $rs = mysqli_query($vConexion, $consulta);

    //3) Si la consulta trae datos, los guardo en el array $Listado
    $i = 0;
    if ($rs && mysqli_num_rows($rs) > 0) {
        while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
            $Listado[$i]['FECHACREACION'] = $data['fecha_detalle']; // Fecha de creación del detalle de licencia
            $Listado[$i]['FECHA_CREACION_DOCUMENTO'] = $data['fecha_documento'];
            $Listado[$i]['NOMBRE'] = $data['empleado_nombre'];
            $Listado[$i]['APELLIDO'] = $data['empleado_apellido'];



            // Manejo de la documentación
            if (!empty($data['Documentacion'])) {
                $Listado[$i]['DOCUMENTACION'] = "<a href='data:application/octet-stream; base64," . base64_encode($data['Documentacion']) . "' download='documentacion_licencia'>Descargar Documentación</a>";
            } else {
                $Listado[$i]['DOCUMENTACION'] = "No hay documentación disponible.";
            }
            $Listado[$i]['IDDETALLELICENCIA'] = $data['iddetalleLicencia'];  // Asegúrate de incluir esta línea
            $Listado[$i]['TIPO_LICENCIA'] = $data['tipo_licencia'];
            $Listado[$i]['ESTADO_LICENCIA'] = $data['estado_licencia'];
            $Listado[$i]['USUARIO'] = $data['user'];
            $i++;
        }
    } else {
        // Si no se encuentran datos, puedo manejarlo de alguna manera, por ejemplo:
        $Listado = array('mensaje' => 'No hay detalles disponibles para esta licencia.');
    }

    //Devuelvo el listado con los detalles de la licencia
    return $Listado;
}
?>

<?php
function Listar_Reporte_Empleado3_Detalle_pdf($vConexion, $idLicencia)
{
    $Listado = array();

    //1) Genero la consulta que deseo
    $consulta = "SELECT 
            dl.descripcion,
            d.Documentacion, -- Ahora obtenemos documentacion desde la tabla 'documento'
            d.FechaCreacion AS fecha_documento, -- Fecha de creación del documento
            dl.FechaCreacion AS fecha_detalle, -- Fecha de creación del detalle de licencia
            e.nombre AS empleado_nombre,
            e.apellido AS empleado_apellido,
            tl.descripcion AS tipo_licencia,
            el.nombreEstado AS estado_licencia
        FROM 
            detallelicencia dl
        LEFT JOIN 
            documento d ON dl.iddetalleLicencia = d.iddetalleLicencia -- Unimos la tabla 'documento' con 'detallelicencia'
        JOIN 
            licencia l ON dl.idLicencia = l.idlicencia
        JOIN 
            empleado e ON l.idEmpleado = e.idempleado
        JOIN 
            tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
        JOIN 
            estadolicencia el ON l.IdEstado = el.idestadoLicencia
        WHERE 
            dl.idLicencia = $idLicencia";

    //2) Ejecuto la consulta y obtengo el resultado
    $rs = mysqli_query($vConexion, $consulta);

    //3) Si la consulta trae datos, los guardo en el array $Listado
    $i = 0;
    if ($rs && mysqli_num_rows($rs) > 0) {
        while ($data = mysqli_fetch_array($rs)) {
            $Listado[$i]['DESCRIPCION'] = $data['descripcion'];
            $Listado[$i]['FECHA_CREACION_DETALLE'] = $data['fecha_detalle'];
            $Listado[$i]['FECHA_CREACION_DOCUMENTO'] = $data['fecha_documento'];
            $Listado[$i]['NOMBRE'] = $data['empleado_nombre'];
            $Listado[$i]['APELLIDO'] = $data['empleado_apellido'];
            $Listado[$i]['DOCUMENTACION'] = $data['Documentacion']; // Ahora la obtienes desde la tabla 'documento'
            $Listado[$i]['TIPO_LICENCIA'] = $data['tipo_licencia'];
            $Listado[$i]['ESTADO_LICENCIA'] = $data['estado_licencia'];
            $i++;
        }
    } else {
        // Si no se encuentran datos, puedo manejarlo de alguna manera, por ejemplo:
        $Listado = array('mensaje' => 'No hay detalles disponibles para esta licencia.');
    }

    //Devuelvo el listado con los detalles de la licencia
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

<?php
function Listar_Reporte_Empleado_Sancion($vConexion, $empleado)
{
    $Listado = array();

    // 1) Genero la consulta
    $consulta = "SELECT 
        s.idsancion, 
        s.fecha_inicio, 
        s.fecha_fin, 
        s.cantidadDias, 
        ts.nombreTipo AS tipo_sancion, 
        es.nombres AS estado_sancion
    FROM sancion s
    JOIN tiposancion ts ON s.IdTipoSancion = ts.idtipoSancion
    JOIN estadosancion es ON s.idEstadoSancion = es.idestadoSancion
    WHERE s.idEmpleado = $empleado
    ORDER BY s.fecha_inicio";

    // 2) Ejecuto la consulta
    $rs = mysqli_query($vConexion, $consulta);

    // 3) Recorro los resultados y los guardo en el array $Listado
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idsancion'];
        $Listado[$i]['FECHA_INICIO'] = $data['fecha_inicio'];
        $Listado[$i]['FECHA_FIN'] = $data['fecha_fin'];
        $Listado[$i]['CANTIDAD_DIAS'] = $data['cantidadDias'];
        $Listado[$i]['TIPO'] = $data['tipo_sancion'];
        $Listado[$i]['ESTADO'] = $data['estado_sancion'];
        $i++;
    }

    // Devuelvo el listado generado
    return $Listado;
}
?>
<?php
function Listar_Reporte_Empleado_HorasExtras($vConexion, $idEmpleado)
{
    $Listado = array();

    // Genero la consulta para obtener las horas extras del empleado
    $consulta = "SELECT 
                    h.idhoraextra, 
                    h.fecha, 
                    h.cantidadHoras, 
                    h.IdEmpleado
                FROM horaextra h
                WHERE h.IdEmpleado = ?
                ORDER BY h.fecha";

    // Preparar la consulta
    $stmt = mysqli_prepare($vConexion, $consulta);

    // Vincular los parámetros (el ID del empleado es un entero)
    mysqli_stmt_bind_param($stmt, "i", $idEmpleado);

    // Ejecutar la consulta
    mysqli_stmt_execute($stmt);

    // Obtener el resultado
    $rs = mysqli_stmt_get_result($stmt);

    // Verificar si la consulta devolvió datos y recorrerlos
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idhoraextra'];
        $Listado[$i]['FECHA'] = $data['fecha'];
        $Listado[$i]['CANTIDAD_HORAS'] = $data['cantidadHoras'];
        $Listado[$i]['IDEMPLEADO'] = $data['IdEmpleado'];
        $i++;
    }

    // Devolver el listado generado (puede estar vacío si no se encuentran registros)
    return $Listado;
}
?>
<?php
function Listar_Reporte_Viaticos_Empleado($vConexion, $idEmpleado)
{
    $Listado = array();

    // Genero la consulta para obtener los viáticos del empleado con su tipo de viático
    $consulta = "SELECT 
                    v.idviatico, 
                    v.fechaotorgamiento, 
                    v.monto, 
                    tv.descripcion AS tipo_viatico
                FROM viatico v
                JOIN tipo_viatico tv ON v.idTipo = tv.idTipo_viatico
                WHERE v.idEmpleado = ?
                ORDER BY v.fechaotorgamiento";

    // Preparar la consulta
    $stmt = mysqli_prepare($vConexion, $consulta);

    // Vincular los parámetros (el ID del empleado es un entero)
    mysqli_stmt_bind_param($stmt, "i", $idEmpleado);

    // Ejecutar la consulta
    mysqli_stmt_execute($stmt);

    // Obtener el resultado
    $rs = mysqli_stmt_get_result($stmt);

    // Recorrer los resultados y guardarlos en el array
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idviatico'];
        $Listado[$i]['FECHA_OTORGAMIENTO'] = $data['fechaotorgamiento'];
        $Listado[$i]['MONTO'] = $data['monto'];
        $Listado[$i]['TIPO_VIATICO'] = $data['tipo_viatico'];
        $i++;
    }

    // Devuelvo el listado generado
    return $Listado;
}
?>

<?php
function Listar_Reporte_Asistencias_Empleado($vConexion, $idEmpleado)
{
    $Listado = array();
    $totalSegundos = 0;

    $consulta = "
        SELECT 
            a.idAsistencia,
            a.fecha,
            ea.nombreEstado AS estado,
            da.observaciones,
            (SELECT horaEvento 
             FROM evento_asistencia 
             WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento = 'Entrada' 
             ORDER BY idEvento ASC LIMIT 1) AS horaEntrada,
            (SELECT horaEvento 
             FROM evento_asistencia 
             WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento = 'Salida' 
             ORDER BY idEvento DESC LIMIT 1) AS horaSalida,
            TIME_TO_SEC(TIMEDIFF(
                (SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento = 'Salida' ORDER BY idEvento DESC LIMIT 1),
                (SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = da.idDetalleAsistencia AND tipoEvento = 'Entrada' ORDER BY idEvento ASC LIMIT 1)
            )) AS segundos_trabajados
        FROM asistencia a
        LEFT JOIN detalle_asistencia da ON a.idAsistencia = da.idAsistencia
        LEFT JOIN estadoasistencia ea ON a.idEstado = ea.idEstado
        WHERE a.idEmpleado = ?
          AND MONTH(a.fecha) = MONTH(CURDATE())
          AND YEAR(a.fecha) = YEAR(CURDATE())
        ORDER BY a.fecha
    ";

    $stmt = mysqli_prepare($vConexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $idEmpleado);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[] = [
            'ID' => $data['idAsistencia'],
            'FECHA' => $data['fecha'],
            'HORA_ENTRADA' => $data['horaEntrada'] ?? '-',
            'HORA_SALIDA' => $data['horaSalida'] ?? '-',
            'ESTADO' => $data['estado'] ?? 'Sin Estado',
            'OBSERVACIONES' => $data['observaciones'] ?? '',
        ];
        $totalSegundos += intval($data['segundos_trabajados']) ?? 0;
    }

    $horas = floor($totalSegundos / 3600);
    $minutos = floor(($totalSegundos % 3600) / 60);

    return [
        'asistencias' => $Listado,
        'total_horas' => "{$horas}h {$minutos}m"
    ];
}
?>
<?php
function Listar_Reporte_Empleado_Vacaciones($vConexion, $empleado)
{
    $Listado = array();

    // 1) Genero la consulta que deseo
    $consulta = "SELECT 
        v.idvacaciones, 
        v.fecha_inicio, 
        v.fecha_fin, 
        v.cantidad_dias, 
        v.estado, 
        v.anio, 
        v.vacaciones_restantes
    FROM 
        vacaciones v
    WHERE 
        v.idempleado = $empleado";

    // 2) A la conexión actual le brindo mi consulta, y el resultado lo entrego a la variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    // 3) El resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[$i]['ID'] = $data['idvacaciones'];
        $Listado[$i]['FECHA_INICIO'] = $data['fecha_inicio'];
        $Listado[$i]['FECHA_FIN'] = $data['fecha_fin'];
        $Listado[$i]['CANTIDAD_DIAS'] = $data['cantidad_dias'];
        $Listado[$i]['ESTADO'] = $data['estado'];
        $Listado[$i]['AÑO'] = $data['anio'];
        $Listado[$i]['VACACIONES_RESTANTES'] = $data['vacaciones_restantes'];
        $i++;
    }
    // Devuelvo el listado generado en el array $Listado (puede estar vacío o contener datos).
    return $Listado;
}
?>




