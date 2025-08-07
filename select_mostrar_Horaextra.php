<?php
function Listar_horaExtradeEmpleado($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM horaextra, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado=horaextra.IdEmpleado   ORDER BY fecha";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro

    $data = mysqli_fetch_array($rs);
    if (!empty($data)) {
        $Listado['NOMBRE'] = $data['nombre'];
        $Listado['APELLIDO'] = $data['apellido'];
        $Listado['ID'] = $data['idempleado'];
        $Listado['IDHORAEXTRA'] = $data['idhoraextra'];
        $Listado['FECHA'] = $data['fecha'];
        $Listado['HORAS'] = $data['cantidadHoras'];
        $Listado['TIPOHORAS'] = $data['tipoHora'];
        $Listado['RECARGO'] = $data['tipoRecargo'];
        $Listado['VALOR'] = $data['valorHoraExtra'];
        $Listado['HORA_INICIO'] = $data['hora_inicio']; 
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}

function ObtenerValoresTipoHora($vConexion)
{
    $valores = array();
    $consulta = "SHOW COLUMNS FROM horaextra LIKE 'tipoHora'";
    $rs = mysqli_query($vConexion, $consulta);

    if ($rs && $data = mysqli_fetch_array($rs)) {
        if (preg_match("/^enum\((.*)\)$/", $data['Type'], $matches)) {
            $enum = explode(",", $matches[1]);
            foreach ($enum as $valor) {
                $valores[] = trim($valor, "'");
            }
        }
    }

    return $valores;
}


function Listar_extras($vConexion)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM horaextra";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $i = 0;
    while ($data = mysqli_fetch_array($rs)) {
        $Listado[$i]['ID'] = $data['idhoraextra'];
        $Listado[$i]['FECHA'] = $data['fecha'];
        $Listado[$i]['CANTIDAD'] = $data['cantidadHoras'];
        $Listado[$i]['TIPO'] = $data['tipoHora'];
        $Listado['RECARGO'] = $data['tipoRecargo'];
        $Listado['VALOR'] = $data['valorHoraExtra'];
        $i++;
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
