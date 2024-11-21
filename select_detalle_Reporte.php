<?php
function Recuperar_detalle_reporte_licencia($vConexion, $empleado, $fechainicio, $fechafin)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT 
    l.idlicencia, 
    l.fechainicio, 
    l.fechafin, 
    el.nombreEstado AS estadoLicencia,  -- Nombre del estado de la licencia
    l.cantidaddias, 
    tl.descripcion AS tipoLicencia, 
    dl.Descripcion AS detalleDescripcion, 
    dl.FechaCreacion
FROM 
    licencia l
JOIN 
    tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
LEFT JOIN 
    detallelicencia dl ON l.idlicencia = dl.idLicencia
JOIN 
    estadolicencia el ON l.IdEstado = el.idestadoLicencia  -- Unir con la tabla de estados
WHERE 
    l.fechainicio >= $fechainicio  -- Fecha de inicio del período
    AND l.fechafin <= $fechafin  -- Fecha de fin del período
    AND l.idEmpleado = $empleado         -- ID del empleado
ORDER BY 
    l.fechainicio";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idlicencia'];
        $Listado[$i]['FECHAINICIO'] = $data['fechainicio'];
        $Listado[$i]['FECHAFIN'] = $data['fechafin'];
        $Listado[$i]['ESTADO'] = $data['estadoLicencia'];
        $Listado[$i]['DIAS'] = $data['cantidaddias'];
        $Listado[$i]['NOMBRETIPO'] = $data['tipoLicencia'];
        $Listado[$i]['DESCRIPCION'] = $data['detalleDescripcion'];
        $Listado[$i]['FECHACREACION'] = $data['fechaCreacion'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>