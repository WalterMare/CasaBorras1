<?php
function InsertarHoraExtra($vConexion) {
    $fecha = $_POST['fecha'];
    $horas = (float)$_POST['horas'];
    $idEmpleado = (int)$_POST['empleado'];
    $tipoHora = CalcularTipoHora($_POST['hora_inicio']);
    $horaInicio = $_POST['hora_inicio'];

    // 1. Obtener el cargo y sueldo del empleado
    $sqlEmpleado = "
        SELECT c.sueldo_basico
        FROM empleado e
        JOIN cargo c ON e.idcargo = c.idcargo
        WHERE e.idempleado = $idEmpleado
        LIMIT 1";
    $resEmp = mysqli_query($vConexion, $sqlEmpleado);
    if (!$resEmp || mysqli_num_rows($resEmp) == 0) {
        die("<h4>Error: no se encontró el empleado o su cargo.</h4>");
    }
    $row = mysqli_fetch_assoc($resEmp);
    $sueldoBasico = (float)$row['sueldo_basico'];

    // 2. Calcular valor de la hora normal
    $valorHoraNormal = $sueldoBasico / 192;

    // 3. Determinar tipo de recargo
    $timestamp = strtotime($fecha);
    $diaSemana = date("w", $timestamp); // 0 (domingo) a 6 (sábado)
    $tipoRecargo = "50%";

    // Feriados definidos manualmente (puede ser una tabla también)
    $feriados = ['2025-01-01', '2025-03-03', '2025-03-04', '2025-03-24', '2025-04-02', '2025-04-17', '2025-04-18','2025-05-01', '2025-05-02', '2025-05-25','2025-06-16', '2025-06-20', '2025-08-15', '2025-08-17', '2025-09-23','2025-09-24', '2025-10-02','2025-10-12', '2025-11-21', '2025-11-24','2025-12-08', '2025-12-25']; // ejemplo

    if (in_array($fecha, $feriados) || $diaSemana == 0) {
        $tipoRecargo = "100%";
    } elseif ($diaSemana == 6) {
        $horaInicio = (int)substr($_POST['hora_inicio'], 0, 2);
        if ($horaInicio >= 13) {
            $tipoRecargo = "100%";
        }
    }

    // 4. Calcular valor hora extra con recargo
    $multiplicador = ($tipoRecargo == '100%') ? 2 : 1.5;
    $valorHoraExtra = $valorHoraNormal * $multiplicador * $horas;

    // 5. Insertar registro
    $sqlInsert = "
        INSERT INTO horaextra (
            idhoraextra, fecha,hora_inicio, cantidadHoras, IdEmpleado, tipoHora, tipoRecargo, valorHoraExtra
        ) VALUES (
            NULL, '$fecha','$horaInicio', $horas, $idEmpleado, '$tipoHora', '$tipoRecargo', $valorHoraExtra
        )";

    if (!mysqli_query($vConexion, $sqlInsert)) {
        die('<h4>Error al intentar insertar el registro: ' . mysqli_error($vConexion) . '</h4>');
    }

    return true;
}

function CalcularTipoHora($hora_inicio) {
  $hora = (int)substr($hora_inicio, 0, 2);
  return ($hora >= 21 || $hora < 6) ? 'Nocturna' : 'Diurna';
}