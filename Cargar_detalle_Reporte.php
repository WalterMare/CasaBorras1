<?php
if (isset($_POST['BotonRegistrar'])) {
    // Obtener los datos del formulario
    $idEmpleado = $_POST['empleado'];
    $idTipoReporte = $_POST['tipo'];
    $fecha = $_POST['fecha'];
    $periodoInicio = $_POST['inicio'];
    $periodoFin = $_POST['fin'];

    // Insertar el reporte
    $query = "INSERT INTO reporte (idreporte, fecha, idTipoReporte,idEmpleado, periodoInicio, periodoFin) 
              VALUES (null, '$fecha', '$idTipoReporte','$idEmpleado', '$periodoInicio', '$periodoFin')";
    $result = mysqli_query($conexion, $query);

    // Obtener el ID del reporte insertado
    $idReporte = mysqli_insert_id($conexion);

    // Verificar si se seleccionaron detalles
    if (isset($_POST['detalles']) && is_array($_POST['detalles'])) {
        // Recoger los detalles enviados
        foreach ($_POST['detalles'] as $detalle) {
            $detalleId = $detalle['id']; // ID del detalle (puede ser de Licencia, Asistencia, etc.)
            $descripcion = $detalle['descripcion']; // Descripción del detalle (por ejemplo, tipo de licencia)

            // Insertar los detalles según el tipo de reporte
            switch ($idTipoReporte) {
                case 1: // Licencia
                    // Aquí tomamos los campos específicos para las licencias
                    $fechainicio = $detalle['fechainicio'];  // Fecha de inicio de la licencia
                    $fechafin = $detalle['fechafin'];        // Fecha de fin de la licencia
                    $idTipoLicencia = $detalle['IdTipo'];    // ID del tipo de licencia
                    $idEstadoLicencia = $detalle['IdEstado']; // Estado de la licencia
                    $cantidadDias = $detalle['cantidaddias']; // Cantidad de días de licencia

                    // Obtener la descripción del tipo de licencia desde la tabla tipolicencia
                    $queryTipoLicencia = "SELECT descripcion FROM tipolicencia WHERE idtipoLicencia = '$idTipoLicencia'";
                    $resultTipoLicencia = mysqli_query($conexion, $queryTipoLicencia);
                    if ($resultTipoLicencia && mysqli_num_rows($resultTipoLicencia) > 0) {
                        $row = mysqli_fetch_assoc($resultTipoLicencia);
                        $descripcionTipoLicencia = $row['descripcion']; // Descripción del tipo de licencia
                    } else {
                        $descripcionTipoLicencia = 'Desconocido'; // Si no se encuentra el tipo de licencia
                    }

                    // Insertar en la tabla detallereporte con los campos específicos de Licencia
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idLicencia, fechainicio, fechafin, IdTipo, IdEstado, cantidaddias, descripcionTipoLicencia)
                                     VALUES ('$idReporte', '$detalleId', '$fechainicio', '$fechafin', '$idTipoLicencia', '$idEstadoLicencia', '$cantidadDias', '$descripcionTipoLicencia')";
                    break;

                case 2: // Asistencia
                    // Si el tipo de reporte es Asistencia, insertar los detalles relacionados a la asistencia
                    $fechaAsistencia = $detalle['fecha'];  // Fecha de la asistencia
                    $idTipoAsistencia = $detalle['idTipoAsistencia']; // Tipo de asistencia
                    $observaciones = $detalle['observaciones']; // Observaciones
                    $justificada = $detalle['justificada']; // Si es justificada (1 o 0)

                    // Obtener el nombre y la descripción del tipo de asistencia desde la tabla tipoasistencia
                    $queryTipoAsistencia = "SELECT nombre, descripcion FROM tipoasistencia WHERE idtipoAsistencia = '$idTipoAsistencia'";
                    $resultTipoAsistencia = mysqli_query($conexion, $queryTipoAsistencia);
                    if ($resultTipoAsistencia && mysqli_num_rows($resultTipoAsistencia) > 0) {
                        $row = mysqli_fetch_assoc($resultTipoAsistencia);
                        $nombreTipoAsistencia = $row['nombre']; // Nombre del tipo de asistencia
                        $descripcionTipoAsistencia = $row['descripcion']; // Descripción del tipo de asistencia
                    } else {
                        $nombreTipoAsistencia = 'Desconocido'; // Si no se encuentra el tipo de asistencia
                        $descripcionTipoAsistencia = 'Descripción no disponible';
                    }

                    // Insertar en la tabla detallereporte con los campos específicos de Asistencia
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idAsistencia, fecha, idTipoAsistencia, observaciones, justificada, nombreTipoAsistencia, descripcionTipoAsistencia)
                                         VALUES ('$idReporte', '$detalleId', '$fechaAsistencia', '$idTipoAsistencia', '$observaciones', '$justificada', '$nombreTipoAsistencia', '$descripcionTipoAsistencia')";
                    break;


                case 3: // Horas Extras
                    // Si el tipo de reporte es Horas Extras, insertar los detalles relacionados a horas extras
                    $fechaHoraExtra = $detalle['fecha']; // Fecha en la que se registraron las horas extras
                    $cantidadHoras = $detalle['cantidadHoras']; // Cantidad de horas extras

                    // Aquí no tenemos una descripción para las horas extras, por lo que vamos a almacenarlas directamente
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idHorasExtras, fecha, cantidadHoras)
                                     VALUES ('$idReporte', '$detalleId', '$fechaHoraExtra', '$cantidadHoras')";
                    break;

                case 4: // Sanción
                    // Obtener los campos específicos de la sanción
                    $fechaInicio = $detalle['fecha_inicio'];  // Fecha de inicio de la sanción
                    $fechaFin = $detalle['fecha_fin'];        // Fecha de fin de la sanción
                    $idTipoSancion = $detalle['idTipoSancion']; // ID del tipo de sanción
                    $descripcionSancion = $detalle['descripcion']; // Descripción de la sanción
                    $cantidadDias = $detalle['cantidadDias']; // Duración de la sanción
                    $idEstadoSancion = $detalle['idEstadoSancion']; // Estado de la sanción

                    // Obtener el nombre del tipo de sanción desde la tabla tiposancion
                    $queryTipoSancion = "SELECT nombreTipo FROM tiposancion WHERE idtipoSancion = '$idTipoSancion'";
                    $resultTipoSancion = mysqli_query($conexion, $queryTipoSancion);
                    if ($resultTipoSancion && mysqli_num_rows($resultTipoSancion) > 0) {
                        $row = mysqli_fetch_assoc($resultTipoSancion);
                        $nombreTipoSancion = $row['nombreTipo']; // Nombre del tipo de sanción
                    } else {
                        $nombreTipoSancion = 'Desconocido'; // Si no se encuentra el tipo de sanción
                    }

                    // Obtener el nombre del estado de la sanción desde la tabla estadosancion
                    $queryEstadoSancion = "SELECT nombres FROM estadosancion WHERE idestadoSancion = '$idEstadoSancion'";
                    $resultEstadoSancion = mysqli_query($conexion, $queryEstadoSancion);
                    if ($resultEstadoSancion && mysqli_num_rows($resultEstadoSancion) > 0) {
                        $row = mysqli_fetch_assoc($resultEstadoSancion);
                        $nombreEstadoSancion = $row['nombres']; // Nombre del estado de la sanción
                    } else {
                        $nombreEstadoSancion = 'Desconocido'; // Si no se encuentra el estado de la sanción
                    }

                    // Insertar en la tabla detallereporte con los campos específicos de Sanción
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idSancion, fecha_inicio, fecha_fin, idTipoSancion, descripcion, cantidadDias, idEstadoSancion, nombreTipoSancion, nombreEstadoSancion)
                                         VALUES ('$idReporte', '$detalleId', '$fechaInicio', '$fechaFin', '$idTipoSancion', '$descripcionSancion', '$cantidadDias', '$idEstadoSancion', '$nombreTipoSancion', '$nombreEstadoSancion')";
                    break;

                case 5: // Embargo
                    // Obtener los campos específicos del embargo
                    $fechaEmbargo = $detalle['fecha'];  // Fecha del embargo
                    $montoEmbargo = $detalle['monto'];  // Monto del embargo
                    $descripcionEmbargo = $detalle['descripcion']; // Descripción del embargo

                    // Insertar en la tabla detallereporte con los campos específicos de Embargo
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idEmbargo, fecha, monto, descripcion)
                                             VALUES ('$idReporte', '$detalleId', '$fechaEmbargo', '$montoEmbargo', '$descripcionEmbargo')";
                    break;
                case 6: // Vacaciones
                    // Obtener los campos específicos de las vacaciones
                    $fechaInicio = $detalle['fechaInicio'];  // Fecha de inicio de las vacaciones
                    $fechaFin = $detalle['fechaFin'];        // Fecha de fin de las vacaciones
                    $diasVacaciones = $detalle['diasVacaciones']; // Días de vacaciones solicitados
                    $idTipoVacaciones = $detalle['idTipoVacaciones']; // Tipo de vacaciones

                    // Obtener la descripción del tipo de vacaciones desde la tabla tipovacaciones
                    $queryTipoVacaciones = "SELECT descripcion FROM tipovacaciones WHERE idtipoVacaciones = '$idTipoVacaciones'";
                    $resultTipoVacaciones = mysqli_query($conexion, $queryTipoVacaciones);
                    if ($resultTipoVacaciones && mysqli_num_rows($resultTipoVacaciones) > 0) {
                        $row = mysqli_fetch_assoc($resultTipoVacaciones);
                        $descripcionTipoVacaciones = $row['descripcion']; // Descripción del tipo de vacaciones
                    } else {
                        $descripcionTipoVacaciones = 'Desconocido'; // Si no se encuentra el tipo de vacaciones
                    }

                    // Insertar en la tabla detallereporte con los campos específicos de Vacaciones
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idVacaciones, fechaInicio, fechaFin, diasVacaciones, descripcionTipoVacaciones)
                                                 VALUES ('$idReporte', '$detalleId', '$fechaInicio', '$fechaFin', '$diasVacaciones', '$descripcionTipoVacaciones')";
                    break;

                case 7: // Viático
                    // Obtener los campos específicos del viático
                    $fechaOtorgamiento = $detalle['fechaOtorgamiento'];  // Fecha de otorgamiento del viático
                    $monto = $detalle['monto'];        // Monto del viático
                    $idTipoViatico = $detalle['idTipo']; // Tipo de viático

                    // Obtener la descripción del tipo de viático desde la tabla tipo_viatico
                    $queryTipoViatico = "SELECT descripcion FROM tipo_viatico WHERE idTipo_viatico = '$idTipoViatico'";
                    $resultTipoViatico = mysqli_query($conexion, $queryTipoViatico);
                    if ($resultTipoViatico && mysqli_num_rows($resultTipoViatico) > 0) {
                        $row = mysqli_fetch_assoc($resultTipoViatico);
                        $descripcionTipoViatico = $row['descripcion']; // Descripción del tipo de viático
                    } else {
                        $descripcionTipoViatico = 'Desconocido'; // Si no se encuentra el tipo de viático
                    }

                    // Insertar en la tabla detallereporte con los campos específicos de Viático
                    $queryDetalle = "INSERT INTO detallereporte (idReporte, idViatico, fechaOtorgamiento, monto, descripcionTipoViatico)
                                         VALUES ('$idReporte', '$detalleId', '$fechaOtorgamiento', '$monto', '$descripcionTipoViatico')";
                    break;

                    // Agregar más casos para otros tipos de reportes, como Embargos, Sanciones, etc.
            }
            // Ejecutar la consulta de inserción de detalles
            mysqli_query($conexion, $queryDetalle);
        }
    }

    // Redirigir o mostrar un mensaje de éxito
    echo "Reporte y detalles guardados correctamente.";
}
