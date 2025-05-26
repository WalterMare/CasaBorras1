<?php
function ObtenerSancion($conexion, $idSancion) {

    $Listado = array();

    // 1) genero la consulta que deseo
    $consulta = "SELECT sancion.*, empleado.nombre, empleado.apellido, 
                        estadosancion.idestadoSancion, estadosancion.nombres AS estado_nombre,
                        tiposancion.nombreTipo AS nombre_tipo
                FROM sancion
                JOIN empleado ON empleado.idempleado = sancion.idEmpleado
                JOIN estadosancion ON estadosancion.idestadoSancion = sancion.idEstadoSancion
                JOIN tiposancion ON tiposancion.idtipoSancion = sancion.IdTipoSancion
                WHERE sancion.idsancion = $idSancion
                ORDER BY sancion.fecha_inicio";

    // 2) a la conexión actual le brindo mi consulta, y el resultado lo entrego a variable $rs
    $rs = mysqli_query($conexion, $consulta);
    
    // 3) el resultado deberá organizarse en una matriz, entonces lo recorro
    $data = mysqli_fetch_array($rs);
    if (!empty($data)) {
        $Listado['NOMBRE'] = $data['nombre'];
        $Listado['APELLIDO'] = $data['apellido'];
        $Listado['ID'] = $data['idEmpleado'];
        $Listado['FECHA_INICIO'] = $data['fecha_inicio'];
        $Listado['FECHA_FIN'] = $data['fecha_fin'];
        $Listado['DESCRIPCION'] = $data['descripcion'];
        $Listado['CANTIDAD_DIAS'] = $data['cantidadDias'];
        $Listado['ID_TIPO_SANCION'] = $data['IdTipoSancion'];
        $Listado['ID_ESTADO_SANCION'] = $data['idEstadoSancion'];
        $Listado['ESTADO_SANCION'] = $data['estado_nombre'];  // Nombre del estado
        $Listado['NOMBRE_TIPO'] = $data['nombre_tipo'];  // Nombre del tipo de sanción
    }

    // devuelvo el listado generado en el array $Listado. (Podrá salir vacío o con datos)
    return $Listado;
}
?>



