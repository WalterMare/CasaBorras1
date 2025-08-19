<?php
function Listar_VacacionesEmpleado($vConexion, $empleado)
{

    $Listado = array();

    $consulta = "SELECT vacaciones.*, empleado.nombre, empleado.apellido 
                 FROM vacaciones
                 INNER JOIN empleado ON empleado.idempleado = vacaciones.idempleado
                 WHERE empleado.idempleado = $empleado
                 ORDER BY vacaciones.anio DESC, vacaciones.fecha_inicio DESC";

    $rs = mysqli_query($vConexion, $consulta);

    if ($rs) {
        while ($data = mysqli_fetch_array($rs)) {
            $Listado[] = array(
                'NOMBRE' => $data['nombre'],
                'APELLIDO' => $data['apellido'],
                'IDEMPLEADO' => $data['idempleado'],
                'IDVACACIONES' => $data['idvacaciones'],
                'FECHA_INICIO' => $data['fecha_inicio'],
                'FECHA_FIN' => $data['fecha_fin'],
                'CANTIDAD_DIAS' => $data['cantidad_dias'],
                'ESTADO' => $data['estado'],
                'ANIO' => $data['anio'],
                'VACACIONES_RESTANTES' => $data['vacaciones_restantes'],
                'OBSERVACIONES' => $data['observaciones'],
                'FECHA_REGISTRO' => $data['fecha_registro']
            );
        }
    }

    return $Listado;
}
?>