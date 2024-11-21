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
    if (isset($_POST['detalles']) && !empty($_POST['detalles'])) {
        // Decodificar el JSON de los detalles
        $detallesSeleccionados = json_decode($_POST['detalles'], true);

        // Recoger los detalles enviados
        foreach ($detallesSeleccionados as $detalle) {
            $detalleId = $detalle['id']; // ID del detalle
            $descripcion = $detalle['descripcion']; // Descripción del detalle

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
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                  -- IdAsistencia
    null,                  -- idLicencias
    NULL,                  -- idViaticos
    NULL,                  -- idSanciones
    NULL,                  -- idHorasExtras
    NULL,                  -- idEmbargos
    '$idReporte',                  -- idReporte
    NULL,                  -- descripcionTipoLicencia
    NULL,                  -- nombreTipoAsistencia
    NULL,                  -- descripcionTipoAsistencia
    NULL,                  -- cantidadHoras
    NULL,                  -- fechaHoraExtra
    '$fechainicio',        -- fecha_inicio (Valor de $fechainicio)
    '$fechafin',           -- fecha_fin (Valor de $fechafin)
    NULL,                  -- idTipoSancion
    NULL,                  -- descripcion
    '$cantidadDias',       -- cantidadDias (Valor de $cantidadDias)
    NULL,                  -- idEstadoSancion
    NULL,                  -- nombreTipoSancion
    NULL,                  -- nombreEstadoSancion
    NULL,                  -- fecha (Dejar NULL si no aplica)
    NULL,                  -- monto
    NULL,                  -- idEmbargo
    NULL,                  -- fechaInicio
    NULL,                  -- fechaFin
    NULL,                  -- diasVacaciones
    NULL,                  -- descripcionTipoVacaciones
    NULL,                  -- idVacaciones
    NULL,                  -- fechaOtorgamiento
    NULL,                  -- descripcionTipoViatico
    NULL,                  -- idViatico
    NULL,                  -- idLicencia
    '$idTipoLicencia',     -- idTipoLicencia (Valor de $idTipoLicencia)
    '$idEstadoLicencia',    -- idEstadoLicencia (Valor de $idEstadoLicencia)
    NULL,   --detalleEmbargo
    NULL             --idtipoviatico
)";
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
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                  -- IdAsistencia (ID de la asistencia, si existe, sino NULL)
    NULL,                  -- idLicencias (ID de la licencia, si existe, sino NULL)
    NULL,                  -- idViaticos (ID de los viáticos, si existe, sino NULL)
    NULL,                  -- idSanciones (ID de las sanciones, si existe, sino NULL)
    NULL,                  -- idHorasExtras (ID de horas extras, si existe, sino NULL)
    NULL,                  -- idEmbargos (ID del embargo, si existe, sino NULL)
    '$idReporte',                  -- idReporte (ID del reporte, si existe, sino NULL)
    NULL,                  -- descripcionTipoLicencia (Descripción de la licencia, si existe, sino NULL)
    '$nombreTipoAsistencia',    -- nombreTipoAsistencia (Valor de $nombreTipoAsistencia)
    '$descripcionTipoAsistencia',  -- descripcionTipoAsistencia (Valor de $descripcionTipoAsistencia)
    NULL,                  -- cantidadHoras (Dejar NULL si no hay horas extras)
    NULL,                  -- fechaHoraExtra (Dejar NULL si no hay fecha de horas extras)
    NULL,                  -- fecha_inicio (Dejar NULL si no aplica)
    NULL,                  -- fecha_fin (Dejar NULL si no aplica)
    NULL,                  -- idTipoSancion (ID del tipo de sanción, si no aplica, dejar NULL)
    NULL,                  -- descripcion (Dejar NULL si no aplica)
    NULL,                  -- cantidadDias (Dejar NULL si no aplica)
    NULL,                  -- idEstadoSancion (Dejar NULL si no aplica)
    NULL,                  -- nombreTipoSancion (Dejar NULL si no aplica)
    NULL,                  -- nombreEstadoSancion (Dejar NULL si no aplica)
    '$fechaAsistencia',    -- fecha (Valor de $fechaAsistencia)
    NULL,                  -- monto (Dejar NULL si no aplica)
    NULL,                  -- idEmbargo (Dejar NULL si no aplica)
    NULL,                  -- fechaInicio (Dejar NULL si no aplica)
    NULL,                  -- fechaFin (Dejar NULL si no aplica)
    NULL,                  -- diasVacaciones (Dejar NULL si no aplica)
    NULL,                  -- descripcionTipoVacaciones (Dejar NULL si no aplica)
    NULL,                  -- idVacaciones (Dejar NULL si no aplica)
    NULL,                  -- fechaOtorgamiento (Dejar NULL si no aplica)
    NULL,                  -- descripcionTipoViatico (Dejar NULL si no aplica)
    NULL,                  -- idViatico (Dejar NULL si no aplica)
    NULL,                  -- idLicencia (Dejar NULL si no aplica)
    NULL,                  -- idTipoLicencia (Dejar NULL si no aplica)
    NULL,                      -- idEstadoLicencia
    NULL,   --detalleEmbargo
    NULL             --idtipoviatico
)";
                    break;


                case 3: // Horas Extras
                    // Si el tipo de reporte es Horas Extras, insertar los detalles relacionados a horas extras
                    $fechaHoraExtra = $detalle['fecha']; // Fecha en la que se registraron las horas extras
                    $cantidadHoras = $detalle['cantidadHoras']; // Cantidad de horas extras

                    // Aquí no tenemos una descripción para las horas extras, por lo que vamos a almacenarlas directamente
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                  -- IdAsistencia
    NULL,                  -- idLicencias
    NULL,                  -- idViaticos
    NULL,                  -- idSanciones
    NULL,                  -- idHorasExtras
    NULL,                  -- idEmbargos
    '$idReporte',                  -- idReporte
    NULL,                  -- descripcionTipoLicencia
    NULL,                  -- nombreTipoAsistencia
    NULL,                  -- descripcionTipoAsistencia
    '$cantidadHoras',      -- cantidadHoras (Valor de $cantidadHoras)
    '$fechaHoraExtra',     -- fechaHoraExtra (Valor de $fechaHoraExtra)
    NULL,                  -- fecha_inicio
    NULL,                  -- fecha_fin
    NULL,                  -- idTipoSancion
    NULL,                  -- descripcion
    NULL,                  -- cantidadDias
    NULL,                  -- idEstadoSancion
    NULL,                  -- nombreTipoSancion
    NULL,                  -- nombreEstadoSancion
    NULL,                  -- fecha
    NULL,                  -- monto
    NULL,                  -- idEmbargo
    NULL,                  -- fechaInicio
    NULL,                  -- fechaFin
    NULL,                  -- diasVacaciones
    NULL,                  -- descripcionTipoVacaciones
    NULL,                  -- idVacaciones
    NULL,                  -- fechaOtorgamiento
    NULL,                  -- descripcionTipoViatico
    NULL,                  -- idViatico
    NULL,                  -- idLicencia
    NULL,                  -- idTipoLicencia
    NULL,                      -- idEstadoLicencia
    NULL,   --detalleEmbargo
    NULL             --idtipoviatico
)";
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
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                  -- IdAsistencia
    NULL,                  -- idLicencias
    NULL,                  -- idViaticos
    NULL,                  -- idSanciones
    NULL,                  -- idHorasExtras
    NULL,                  -- idEmbargos
    '$idReporte',                  -- idReporte
    NULL,                  -- descripcionTipoLicencia
    NULL,                  -- nombreTipoAsistencia
    NULL,                  -- descripcionTipoAsistencia
    NULL,                  -- cantidadHoras
    NULL,                  -- fechaHoraExtra
    '$fechaInicio',        -- fecha_inicio 
    '$fechaFin',           -- fecha_fin 
    '$idTipoSancion',      -- idTipoSancion 
    '$descripcionSancion', -- descripcion 
    '$cantidadDias',       -- cantidadDias 
    '$idEstadoSancion',    -- idEstadoSancion 
    '$nombreTipoSancion',  -- nombreTipoSancion 
    '$nombreEstadoSancion',                  -- nombreEstadoSancion
    NULL,                  -- fecha
    NULL,                  -- monto
    NULL,                  -- idEmbargo
    NULL,                  -- fechaInicio
    NULL,                  -- fechaFin
    NULL,                  -- diasVacaciones
    NULL,                  -- descripcionTipoVacaciones
    NULL,                  -- idVacaciones
    NULL,                  -- fechaOtorgamiento
    NULL,                  -- descripcionTipoViatico
    NULL,                  -- idViatico
    NULL,                  -- idLicencia
    NULL,                  -- idTipoLicencia
    NULL,                      -- idEstadoLicencia
    NULL,   --detalleEmbargo
    NULL             --idtipoviatico
)";

                    break;

                case 5: // Embargo
                    // Obtener los campos específicos del embargo
                    $fechaEmbargo = $detalle['fecha'];  // Fecha del embargo
                    $montoEmbargo = $detalle['monto'];  // Monto del embargo
                    $descripcionEmbargo = $detalle['descripcion']; // Descripción del embargo

                    // Insertar en la tabla detallereporte con los campos específicos de Embargo
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                  -- IdAsistencia
    NULL,                  -- idLicencias
    NULL,                  -- idViaticos
    NULL,                  -- idSanciones
    NULL,                  -- idHorasExtras
    NULL,                  -- idEmbargos
    '$idReporte',                  -- idReporte
    NULL,                  -- descripcionTipoLicencia
    NULL,                  -- nombreTipoAsistencia
    NULL,                  -- descripcionTipoAsistencia
    NULL,                  -- cantidadHoras
    NULL,                  -- fechaHoraExtra
    NULL,                  -- fecha_inicio
    NULL,                  -- fecha_fin
    NULL,                  -- idTipoSancion
    NULL,                  -- descripcion
    NULL,                  -- cantidadDias
    NULL,                  -- idEstadoSancion
    NULL,                  -- nombreTipoSancion
    NULL,                  -- nombreEstadoSancion
    NULL,                  -- fecha
    NULL,                  -- monto
    NULL,                  -- idEmbargo
    '$fechaEmbargo',       -- fecha (Fecha del embargo) 
    '$montoEmbargo',                  -- monto (NULL ya que no se tiene valor para esta columna
    NULL,                  -- idEmbargo (NULL, no se tiene valor)
    NULL,                  -- fechaInicio
    NULL,                  -- fechaFin
    NULL,                  -- diasVacaciones
    NULL,                  -- descripcionTipoVacaciones
    NULL,                  -- idVacaciones
    NULL,                  -- fechaOtorgamiento
    NULL,                  -- descripcionTipoViatico
    NULL,                  -- idViatico
    NULL,                  -- idLicencia
    NULL,                  -- idTipoLicencia
    NULL,                   -- idEstadoLicencia
    '$descripcionEmbargo',   --detalleEmbargo
     NULL             --idtipoviatico
)";
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
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                     -- IdAsistencia
    NULL,                     -- idLicencias
    NULL,                     -- idViaticos
    NULL,                     -- idSanciones
    NULL,                     -- idHorasExtras
    NULL,                     -- idEmbargos
    '$idReporte',                     -- idReporte
    NULL,                     -- descripcionTipoLicencia
    NULL,                     -- nombreTipoAsistencia
    NULL,                     -- descripcionTipoAsistencia
    NULL,                     -- cantidadHoras
    NULL,                     -- fechaHoraExtra
    NULL,                     -- fecha_inicio
    NULL,                     -- fecha_fin
    NULL,                     -- idTipoSancion
    NULL,                     -- descripcion
    NULL,                     -- cantidadDias
    NULL,                     -- idEstadoSancion
    NULL,                     -- nombreTipoSancion
    NULL,                     -- nombreEstadoSancion
    NULL,                     -- fecha
    NULL,                     -- monto
    NULL,                     -- idEmbargo
    '$fechaInicio',           -- fechaInicio (Fecha de inicio de las vacaciones)
    '$fechaFin',              -- fechaFin (Fecha de fin de las vacaciones)
    '$diasVacaciones',        -- diasVacaciones (Días de vacaciones solicitados)
    '$descripcionTipoVacaciones', -- descripcionTipoVacaciones (Descripción del tipo de vacaciones)
    NULL,                     -- idVacaciones (Si tienes valor para este campo, reemplázalo aquí)
    NULL,                     -- fechaOtorgamiento
    NULL,                     -- descripcionTipoViatico
    NULL,                     -- idViatico
    NULL,                     -- idLicencia
    NULL,                     -- idTipoLicencia
    NULL,                      -- idEstadoLicencia
    NULL,   --detalleEmbargo
    NULL             --idtipoviatico
)";

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
                    $queryDetalle = "INSERT INTO detallereporte (
    IdAsistencia,
    idLicencias,
    idViaticos,
    idSanciones,
    idHorasExtras,
    idEmbargos,
    idReporte,
    descripcionTipoLicencia,
    nombreTipoAsistencia,
    descripcionTipoAsistencia,
    cantidadHoras,
    fechaHoraExtra,
    fecha_inicio,
    fecha_fin,
    idTipoSancion,
    descripcion,
    cantidadDias,
    idEstadoSancion,
    nombreTipoSancion,
    nombreEstadoSancion,
    fecha,
    monto,
    idEmbargo,
    fechaInicio,
    fechaFin,
    diasVacaciones,
    descripcionTipoVacaciones,
    idVacaciones,
    fechaOtorgamiento,
    descripcionTipoViatico,
    idViatico,
    idLicencia,
    idTipoLicencia,
    idEstadoLicencia
)
VALUES (
    NULL,                         -- IdAsistencia
    NULL,                         -- idLicencias
    NULL,                         -- idViaticos
    NULL,                         -- idSanciones
    NULL,                         -- idHorasExtras
    NULL,                         -- idEmbargos
    NULL,                         -- idReporte
    NULL,                         -- descripcionTipoLicencia
    NULL,                         -- nombreTipoAsistencia
    NULL,                         -- descripcionTipoAsistencia
    NULL,                         -- cantidadHoras
    NULL,                         -- fechaHoraExtra
    NULL,                         -- fecha_inicio
    NULL,                         -- fecha_fin
    NULL,                         -- idTipoSancion
    NULL,                         -- descripcion
    NULL,                         -- cantidadDias
    NULL,                         -- idEstadoSancion
    NULL,                         -- nombreTipoSancion
    NULL,                         -- nombreEstadoSancion
    NULL,                         -- fecha
    '$monto',                     -- monto (Monto del viático)
    NULL,                         -- idEmbargo
    NULL,                         -- fechaInicio
    NULL,                         -- fechaFin
    NULL,                         -- diasVacaciones
    NULL,                         -- descripcionTipoVacaciones
    NULL,                         -- idVacaciones
    '$fechaOtorgamiento',                         -- fechaOtorgamiento
    '$descripcionTipoViatico',    -- descripcionTipoViatico (Descripción del tipo de viático)
    NULL,                         -- idViatico (Si tienes valor para este campo, reemplázalo aquí)
    NULL,                         -- idLicencia
    NULL,                         -- idTipoLicencia
    NULL                          -- idEstadoLicencia
    '$idTipoViatico'              --idtipoviatico
)";

                    break;

                    // Agregar más casos para otros tipos de reportes, como Embargos, Sanciones, etc.
            }
            // Ejecutar la consulta de inserción de detalles
            if (isset($queryDetalle)) {
                mysqli_query($conexion, $queryDetalle);
            }
        }
    }

    // Redirigir o mostrar un mensaje de éxito
    echo "Reporte y detalles guardados correctamente.";
}
