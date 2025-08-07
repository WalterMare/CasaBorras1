
<?php
function Listar_horaExtraEmpleado($vConexion, $empleado)
{

    $Listado = array();

    $consulta = "SELECT * FROM horaextra, empleado 
                 WHERE empleado.idempleado = $empleado
                 AND empleado.idempleado = horaextra.IdEmpleado 
                 ORDER BY fecha";

    $rs = mysqli_query($vConexion, $consulta);

    if ($rs) {
        while ($data = mysqli_fetch_array($rs)) {
            $Listado[] = array(
                'NOMBRE' => $data['nombre'],
                'APELLIDO' => $data['apellido'],
                'ID' => $data['idempleado'],
                'IDHORAEXTRA' => $data['idhoraextra'],
                'FECHA' => $data['fecha'],
                'HORAS' => $data['cantidadHoras'],
                'TIPOHORAS' => $data['tipoHora'],
                'RECARGO' => $data['tipoRecargo'],
                'VALOR' => $data['valorHoraExtra']
            );
        }
    }

    return $Listado;
}
?>
<?php
function ObtenerValoresEnumTipoHora($conexion)
{
    $sql = "SHOW COLUMNS FROM horaextra LIKE 'tipoHora'";
    $resultado = $conexion->query($sql);

    $valores = [];
    if ($resultado && $fila = $resultado->fetch_assoc()) {
        $tipoEnum = $fila['Type']; // Ej: enum('Diurna','Nocturna')

        // Extraer valores del enum
        preg_match_all("/'([^']+)'/", $tipoEnum, $matches);
        $valores = $matches[1];
    }
    return $valores;
}

function ObtenerHoraExtraPorID($vConexion, $idHoraExtra) {
    $consulta = "SELECT 
                    he.idhoraextra AS IDHORAEXTRA,
                    he.fecha AS FECHA,
                    he.cantidadHoras AS HORAS,
                    he.tipoHora AS TIPOHORAS,
                    he.tipoRecargo AS RECARGO,
                    he.valorHoraExtra AS VALOR,
                    he.IdEmpleado AS ID,
                    he.hora_inicio AS HORAINICIO,
                    e.nombre AS NOMBRE,
                    e.apellido AS APELLIDO
                 FROM horaextra he
                 JOIN empleado e ON e.idempleado = he.IdEmpleado
                 WHERE he.idhoraextra = $idHoraExtra
                 LIMIT 1";

    $rs = mysqli_query($vConexion, $consulta);

    if ($rs && mysqli_num_rows($rs) > 0) {
        return mysqli_fetch_assoc($rs);
    }

    return false;
}

?>