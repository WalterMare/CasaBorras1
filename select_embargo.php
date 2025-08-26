<?php
function Listar_EmbargoEmpleado($vConexion, $idEmpleado)
{
    $Listado = array();

    // Consulta mejorada usando JOIN explícito y trayendo todas las columnas
    $consulta = "SELECT e.idembargo, e.fecha, e.expediente, e.tipo, e.fecha_inicio, e.fecha_fin,
                        e.monto, e.porcentaje, e.estado, e.descripcion,
                        emp.idempleado, emp.nombre, emp.apellido
                 FROM embargo e
                 INNER JOIN empleado emp ON emp.idempleado = e.idEmpleado
                 WHERE emp.idempleado = $idEmpleado
                 ORDER BY e.fecha";

    $rs = mysqli_query($vConexion, $consulta);

    // Recorremos los resultados y armamos el array
    while ($data = mysqli_fetch_assoc($rs)) {
        $Listado[] = array(
            'IDEMBARGO'     => $data['idembargo'],
            'IDEMPLEADO'    => $data['idempleado'],
            'NOMBRE'        => $data['nombre'],
            'APELLIDO'      => $data['apellido'],
            'FECHA'         => $data['fecha'],
            'EXPEDIENTE'    => $data['expediente'],
            'TIPO'          => $data['tipo'],
            'FECHA_INICIO'  => $data['fecha_inicio'],
            'FECHA_FIN'     => $data['fecha_fin'],
            'MONTO'         => $data['monto'],
            'PORCENTAJE'    => $data['porcentaje'],
            'ESTADO'        => $data['estado'],
            'DESCRIPCION'   => $data['descripcion']
        );
    }

    // Devolver listado (puede estar vacío)
    return !empty($Listado) ? $Listado : false;
}
?>

<?php
function Listar_EmbargodeEmpleado($vConexion, $empleado)
{

    $Listado = array();

    //1) genero la consulta que deseo
    $consulta = "SELECT * FROM embargo, empleado WHERE empleado.idempleado=$empleado
    and empleado.idempleado= embargo.idEmpleado   ORDER BY fecha";

    //2) a la conexion actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($vConexion, $consulta);

    //3) el resultado deberá organizarse en una matriz, entonces lo recorro

    $data = mysqli_fetch_array($rs);
    if (!empty($data)) {
        $Listado['NOMBRE'] = $data['nombre'];
        $Listado['APELLIDO'] = $data['apellido'];
        $Listado['ID'] = $data['idempleado'];
        $Listado['FECHA'] = $data['fecha'];
        $Listado['MONTO'] = $data['monto'];
        $Listado['DESCRIPCION'] = $data['descripcion'];
    }


    //devuelvo el listado generado en el array $Listado. (Podra salir vacio o con datos)..
    return $Listado;
}
?>


<?php
function ObtenerEmbargoPorID($vConexion, $idEmbargo)
{

    $Listado = array();

    $consulta = "SELECT e.idempleado, e.nombre, e.apellido,
                        em.idembargo, em.expediente, em.tipo, em.fecha, 
                        em.fecha_inicio, em.fecha_fin, em.estado, em.monto, 
                        em.porcentaje, em.descripcion
                 FROM empleado e
                 INNER JOIN embargo em ON e.idempleado = em.idEmpleado
                 WHERE em.idembargo = ?
                 LIMIT 1";

    if ($stmt = mysqli_prepare($vConexion, $consulta)) {
        mysqli_stmt_bind_param($stmt, "i", $idEmbargo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($data = mysqli_fetch_assoc($result)) {
            $Listado = array(
                'IDEMPLEADO'   => $data['idempleado'],
                'NOMBRE'       => $data['nombre'],
                'APELLIDO'     => $data['apellido'],
                'IDEMBARGO'    => $data['idembargo'],
                'EXPEDIENTE'   => $data['expediente'],
                'TIPO'         => $data['tipo'],
                'FECHA'        => $data['fecha'],
                'FECHA_INICIO' => $data['fecha_inicio'],
                'FECHA_FIN'    => $data['fecha_fin'],
                'ESTADO'       => $data['estado'],
                'MONTO'        => $data['monto'],
                'PORCENTAJE'   => $data['porcentaje'],
                'DESCRIPCION'  => $data['descripcion']
            );
        }

        mysqli_stmt_close($stmt);
    }

    return $Listado; // Array con todos los datos del embargo
}
?>

<?php
function Eliminar_Consulta($vConexion, $idEmbargo)
{
    // Validar que $idEmbargo sea numérico
    $idEmbargo = intval($idEmbargo);

    // 1) Verificar que exista el embargo
    $consulta = "SELECT idembargo FROM embargo WHERE idembargo = $idEmbargo";
    $rs = mysqli_query($vConexion, $consulta);

    $data = mysqli_fetch_array($rs);
    if (!empty($data['idembargo'])) {
        // 2) Eliminar solo el embargo específico
        $delete = "DELETE FROM embargo WHERE idembargo = $idEmbargo";
        if (mysqli_query($vConexion, $delete)) {
            return true;
        } else {
            return false; // error en el DELETE
        }
    } else {
        return false; // no existe el embargo
    }
}
?>