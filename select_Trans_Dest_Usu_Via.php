<?php

function Listar_Reporte_2($vConexion, $usuario, $filtro_estado = 'todos',$filtro_documento = '')
{
    $Listado = array();

    // Base de la consulta
    $SQL = "
        SELECT 
            E.nombre, 
            E.apellido AS apellido, 
            E.idempleado, 
            E.estado, 
            E.fecha_inicio, 
            E.ciudad, 
            E.tel, 
            E.imagen, 
            E.fecha_baja,
            E.dni,
            C.descripcion AS nomcargo, 
            C.idcargo, 
            P.idprovincia, 
            P.nombre AS nomprov
        FROM 
            empleado E
        JOIN 
            cargo C ON C.idcargo = E.Idcargo
        JOIN 
            provincia P ON E.idprovincia = P.idprovincia
    ";

    // Arreglo de condiciones dinámicas
    $condiciones = array();

    // Filtro por estado
    if ($filtro_estado == 'activos') {
        $condiciones[] = "E.estado = 1";
    } elseif ($filtro_estado == 'inactivos') {
        $condiciones[] = "E.estado = 0 AND E.fecha_baja IS NULL";
    } elseif ($filtro_estado == 'baja') {
        $condiciones[] = "E.estado = 0 AND E.fecha_baja IS NOT NULL";
    }

    // Filtro por documento
    if (!empty($filtro_documento) && ctype_digit($filtro_documento)) {
        $condiciones[] = "E.dni = " . intval($filtro_documento);
    }

    // Unir condiciones si hay alguna
    if (!empty($condiciones)) {
        $SQL .= " WHERE " . implode(" AND ", $condiciones);
    }

    $SQL .= " ORDER BY apellido;";

    // Ejecutar consulta
    $rs = mysqli_query($vConexion, $SQL);

    // Procesar resultados
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['NOMBRE'] = $data['nombre'];
        $Listado[$i]['APELLIDO'] = $data['apellido'];
        $Listado[$i]['ESTADO'] = $data['estado'];
        $Listado[$i]['FECHAINICIO'] = date("d/m/Y", strtotime($data['fecha_inicio']));
        $Listado[$i]['CIUDAD'] = $data['ciudad'];
        $Listado[$i]['DOCUMENTO'] = $data['dni'];
        $Listado[$i]['IMAGEN'] = $data['imagen'];
        $Listado[$i]['CARGO'] = $data['nomcargo'];
        $Listado[$i]['PROVINCIA'] = $data['nomprov'];
        $Listado[$i]['ID'] = $data['idempleado'];
        $Listado[$i]['FECHABAJA'] = !empty($data['fecha_baja']) ? date("d/m/Y", strtotime($data['fecha_baja'])) : 'N/A';
        $i++;
    }

    return $Listado;
}

