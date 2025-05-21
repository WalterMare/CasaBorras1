<?php


function registrarEntrada($idEmpleado, $conexion)
{
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $idAsistencia = null;
    $idDetalle = null;

    // Verificar si ya tiene asistencia hoy
    $stmt = $conexion->prepare("SELECT idAsistencia FROM asistencia WHERE idEmpleado = ? AND fecha = ?");
    $stmt->bind_param("is", $idEmpleado, $fecha);
    $stmt->execute();
    $stmt->bind_result($idAsistencia);
    $stmt->fetch();
    $stmt->close();

    if (!$idAsistencia) {
        $stmt = $conexion->prepare("INSERT INTO asistencia (idEmpleado, fecha, idEstado) VALUES (?, ?, 1)");
        $stmt->bind_param("is", $idEmpleado, $fecha);
        $stmt->execute();
        $idAsistencia = $stmt->insert_id;
        $stmt->close();
    }

    // Buscar o crear detalle de asistencia
    $stmt = $conexion->prepare("SELECT idDetalleAsistencia FROM detalle_asistencia WHERE idAsistencia = ?");
    $stmt->bind_param("i", $idAsistencia);
    $stmt->execute();
    $stmt->bind_result($idDetalle);
    $stmt->fetch();
    $stmt->close();

    if (!$idDetalle) {
        $stmt = $conexion->prepare("INSERT INTO detalle_asistencia (idAsistencia) VALUES (?)");
        $stmt->bind_param("i", $idAsistencia);
        $stmt->execute();
        $idDetalle = $stmt->insert_id;
        $stmt->close();
    }

    // ✅ Verificar si ya hay una entrada registrada para este detalle
    $stmt = $conexion->prepare("SELECT idEvento FROM evento_asistencia WHERE idDetalleAsistencia = ? AND tipoEvento = 'Entrada'");
    $stmt->bind_param("i", $idDetalle);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Ya existe una entrada registrada
        $stmt->close();
        return "Ya se registró una entrada hoy."; 
    }
    $stmt->close();

    // Registrar evento de entrada
    $stmt = $conexion->prepare("INSERT INTO evento_asistencia (idDetalleAsistencia, tipoEvento, horaEvento) VALUES (?, 'Entrada', ?)");
    $stmt->bind_param("is", $idDetalle, $hora);
    $stmt->execute();
    $stmt->close();
  
     return "Entrada registrada correctamente.";
}

function registrarSalida($idEmpleado, $conexion)
{
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $idAsistencia = null;
    $idDetalle = null;
    $horaEntradaStr = null;

    // Obtener idAsistencia y idDetalle
    $stmt = $conexion->prepare("SELECT a.idAsistencia, da.idDetalleAsistencia 
                            FROM asistencia a
                            INNER JOIN detalle_asistencia da ON a.idAsistencia = da.idAsistencia
                            WHERE a.idEmpleado = ? AND a.fecha = ?");
    $stmt->bind_param("is", $idEmpleado, $fecha);
    $stmt->execute();
    $stmt->bind_result($idAsistencia, $idDetalle);
    $stmt->fetch();
    $stmt->close();

    if ($idDetalle) {
        // Registrar evento de salida
        $stmt = $conexion->prepare("INSERT INTO evento_asistencia (idDetalleAsistencia, tipoEvento, horaEvento) VALUES (?, 'Salida', ?)");
        $stmt->bind_param("is", $idDetalle, $hora);
        $stmt->execute();
        $stmt->close();

        // Buscar hora de entrada
        $stmt = $conexion->prepare("SELECT horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = ? AND tipoEvento = 'Entrada' ORDER BY idEvento ASC LIMIT 1");
        $stmt->bind_param("i", $idDetalle);
        $stmt->execute();
        $stmt->bind_result($horaEntradaStr);
        $stmt->fetch();
        $stmt->close();

        if ($horaEntradaStr) {
            $horaEntrada = new DateTime($horaEntradaStr);
            $horaSalida = new DateTime($hora);
            $intervalo = $horaEntrada->diff($horaSalida);
            $horasTrabajadas = $intervalo->format('%H:%I:%S');

            // Comparar con horario esperado
            // Traducir el día de la semana al español manualmente
            $dias = [
                'Sunday' => 'Domingo',
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado'
            ];
            $diaSemanaIngles = date('l'); // Día en inglés como 'Monday'
            $diaSemana = $dias[$diaSemanaIngles]; // Día traducido como 'Lunes'
            $horario = $conexion->query("SELECT COALESCE(edh.hora_inicio, tdh.hora_inicio) AS inicio, COALESCE(edh.hora_fin, tdh.hora_fin) AS fin
                FROM empleado e
                LEFT JOIN empleado_turno et ON e.idempleado = et.idempleado
                LEFT JOIN turno_dia_horario tdh ON et.idturno = tdh.idturno AND tdh.dia_semana = '$diaSemana'
                LEFT JOIN empleado_dia_horario edh ON e.idempleado = edh.idempleado AND edh.dia_semana = '$diaSemana'
                WHERE e.idempleado = $idEmpleado")->fetch_assoc();

            $observacion = '';
            if ($horario) {
                if ($horaEntrada > new DateTime($horario['inicio'])) {
                    $observacion .= "Llegó tarde. ";
                }
                if ($horaSalida < new DateTime($horario['fin'])) {
                    $observacion .= "Se fue antes. ";
                }
            }

            $stmt = $conexion->prepare("UPDATE detalle_asistencia SET horasTrabajadas = ?, observaciones = ? WHERE idDetalleAsistencia = ?");
            $stmt->bind_param("ssi", $horasTrabajadas, $observacion, $idDetalle);
            $stmt->execute();
            $stmt->close();

            $stmt = $conexion->prepare("UPDATE asistencia SET idEstado = 1 WHERE idAsistencia = ?");
            $stmt->bind_param("i", $idAsistencia);
            $stmt->execute();
            $stmt->close();
        }
    }
}
