<?php
require_once 'conexiondb.php';
$conexion = ConexionBD();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = strtolower(trim($_POST['mensaje']));
    $respuesta = "Lo siento, no entendí tu consulta.";

    // Detectar si pregunta si alguien vino hoy
    if (preg_match('/vino hoy ([a-záéíóúñ\s]+)/i', $mensaje, $coincidencia)) {
        $nombreCompleto = ucwords(trim($coincidencia[1]));
        $partes = explode(" ", $nombreCompleto);
        if (count($partes) >= 2) {
            $nombre = $partes[0];
            $apellido = $partes[1];
            $fecha = date('Y-m-d');

            $stmt = mysqli_prepare(
                $conexion,
                "SELECT ea.nombreEstado 
             FROM asistencia a 
             JOIN empleado e ON a.idEmpleado = e.idempleado 
             JOIN estadoasistencia ea ON a.idEstado = ea.idEstado 
             WHERE e.nombre = ? AND e.apellido = ? AND a.fecha = ?"
            );

            mysqli_stmt_bind_param($stmt, 'sss', $nombre, $apellido, $fecha);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $estado);
            if (mysqli_stmt_fetch($stmt)) {
                $respuesta = "$nombre $apellido si esta $estado.";
            } else {
                $respuesta = "$nombre $apellido no registró asistencia hoy.";
            }
            mysqli_stmt_close($stmt);
        }
    }


    // Consultar si un empleado está de vacaciones hoy
    elseif (preg_match('/de vacaciones\s+([a-záéíóúñ]+\s[a-záéíóúñ]+)/i', $mensaje, $coincidencia)) {
        $nombreCompleto = ucwords(trim($coincidencia[1]));
        $partes = explode(" ", $nombreCompleto);
        if (count($partes) >= 2) {
            $nombre = $partes[0];
            $apellido = $partes[1];

            $hoy = date('Y-m-d');

            // Verifica si el empleado está de vacaciones hoy
            $stmt = mysqli_prepare($conexion, "
                SELECT v.fecha_inicio, v.fecha_fin, v.cantidad_dias, e.idempleado
                FROM vacaciones v
                JOIN empleado e ON v.idempleado = e.idempleado
                WHERE e.nombre = ? AND e.apellido = ?
                AND ? BETWEEN v.fecha_inicio AND v.fecha_fin
                LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, 'sss', $nombre, $apellido, $hoy);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $fechaInicio, $fechaFin, $diasTomados, $idEmpleado);

            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);

                // Año al que corresponde la vacación (año anterior a la fecha de inicio)
                $anioCorrespondiente = date('Y', strtotime($fechaInicio)) - 1;
                $limiteUso = ($anioCorrespondiente + 1) . "-04-01"; // 1 de abril del año siguiente

                if ($hoy >= $limiteUso) {
                    $respuesta = "$nombre $apellido está de vacaciones hoy, pero ya no debería estar usándolas porque vencían el 31 de marzo de " . ($anioCorrespondiente + 1) . ".";
                } else {
                    // Traer todas las vacaciones tomadas del año correspondiente
                    $stmt2 = mysqli_prepare($conexion, "
                        SELECT SUM(v.cantidad_dias)
                        FROM vacaciones v
                        WHERE v.idempleado = ? AND YEAR(v.fecha_inicio) = ?
                    ");
                    mysqli_stmt_bind_param($stmt2, 'ii', $idEmpleado, $anioCorrespondiente + 1); // vacaciones tomadas entre oct-abril
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_bind_result($stmt2, $totalTomado);
                    mysqli_stmt_fetch($stmt2);
                    mysqli_stmt_close($stmt2);

                    // Definimos que el total disponible es 14 días (puede ajustarse si tenés un campo para eso)
                    $totalDisponible = 14;
                    $diasRestantes = $totalDisponible - $totalTomado;

                    $respuesta = "$nombre $apellido está de vacaciones hoy. Tomó $diasTomados día(s) en este tramo, correspondientes al año $anioCorrespondiente. Le quedan $diasRestantes día(s) disponibles para usar antes del 31 de marzo de " . ($anioCorrespondiente + 1) . ".";
                }
            } else {
                mysqli_stmt_close($stmt);
                $respuesta = "$nombre $apellido no está de vacaciones hoy.";
            }
        } else {
            $respuesta = "Por favor, ingresá nombre y apellido para consultar vacaciones.";
        }
    }

    // Consultar turno del empleado
    elseif (preg_match('/turno tiene\s+([a-záéíóúñ]+\s[a-záéíóúñ]+)/i', $mensaje, $coincidencia)) {
        $nombreCompleto = ucwords(trim($coincidencia[1]));
        $partes = explode(" ", $nombreCompleto);
        if (count($partes) >= 2) {
            $nombre = $partes[0];
            $apellido = $partes[1];

            $query = "SELECT t.nombre FROM turno t 
                  JOIN empleado_turno et ON t.idturno = et.idturno 
                  JOIN empleado e ON et.idempleado = e.idempleado 
                  WHERE e.nombre = ? AND e.apellido = ? 
                  ORDER BY et.fecha_asignacion DESC LIMIT 1";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $nombre, $apellido);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $turno);
            if (mysqli_stmt_fetch($stmt)) {
                $respuesta = "$nombre $apellido tiene asignado el turno: $turno.";
            } else {
                $respuesta = "$nombre $apellido no tiene un turno asignado.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Consultar si tiene licencia hoy
    elseif (preg_match('/licencia hoy\s+([a-záéíóúñ]+\s[a-záéíóúñ]+)/i', $mensaje, $coincidencia)) {
        $nombreCompleto = ucwords(trim($coincidencia[1]));
        $partes = explode(" ", $nombreCompleto);
        if (count($partes) >= 2) {
            $nombre = $partes[0];
            $apellido = $partes[1];
            $fecha = date('Y-m-d');

            $query = "SELECT COUNT(*) FROM licencia l JOIN empleado e ON l.idEmpleado = e.idempleado 
                  WHERE e.nombre = ? AND e.apellido = ? AND ? BETWEEN l.fechainicio AND l.fechafin";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $nombre, $apellido, $fecha);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $tieneLicencia);
            mysqli_stmt_fetch($stmt);
            $respuesta = $tieneLicencia > 0 ? "$nombre $apellido tiene licencia hoy." : "$nombre $apellido no tiene licencia hoy.";
            mysqli_stmt_close($stmt);
        }
    }

    // Consultar cantidad de sanciones
    elseif (preg_match('/sanciones tiene\s+([a-záéíóúñ]+\s[a-záéíóúñ]+)/i', $mensaje, $coincidencia)) {
        $nombreCompleto = ucwords(trim($coincidencia[1]));
        $partes = explode(" ", $nombreCompleto);
        if (count($partes) >= 2) {
            $nombre = $partes[0];
            $apellido = $partes[1];

            $query = "SELECT COUNT(*) FROM sancion s 
                  JOIN empleado e ON s.idEmpleado = e.idempleado 
                  WHERE e.nombre = ? AND e.apellido = ?";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $nombre, $apellido);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $cantidad);
            mysqli_stmt_fetch($stmt);
            $respuesta = "$nombre $apellido tiene $cantidad sanción(es).";
            mysqli_stmt_close($stmt);
        }
    }
    // Consultar próxima fecha de vacaciones
    elseif (preg_match('/cuando empieza las vacaciones\s+([a-záéíóúñ]+\s[a-záéíóúñ]+)/i', $mensaje, $coincidencia)) {
        $nombreCompleto = ucwords(trim($coincidencia[1]));
        $partes = explode(" ", $nombreCompleto);
        if (count($partes) >= 2) {
            $nombre = $partes[0];
            $apellido = $partes[1];
            $hoy = date('Y-m-d');

            $query = "SELECT MIN(v.fecha_inicio) FROM vacaciones v 
                  JOIN empleado e ON v.idempleado = e.idempleado 
                  WHERE e.nombre = ? AND e.apellido = ? AND v.fecha_inicio >= ?";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $nombre, $apellido, $hoy);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $fechaVacacion);
            mysqli_stmt_fetch($stmt);
            $respuesta = $fechaVacacion ? "$nombre $apellido empieza sus vacaciones el $fechaVacacion." : "$nombre $apellido no tiene vacaciones próximas.";
            mysqli_stmt_close($stmt);
        }
    }

    // Menú de ayuda con opciones disponibles
    elseif (preg_match('/(ayuda|menu|opciones|qué puedo preguntar)/i', $mensaje)) {
        $respuesta = "Estas son algunas preguntas que podés hacer:<br><br>" .
            "📌 ¿Vino hoy [Nombre Apellido]?<br>" .
            "📌 ¿Está de vacaciones [Nombre Apellido]?<br>" .
            "📌 ¿Tiene licencia hoy [Nombre Apellido]?<br>" .
            "📌 ¿Qué turno tiene [Nombre Apellido]?<br>" .
            "📌 ¿Cuántas sanciones tiene [Nombre Apellido]?<br>" .
            "📌 ¿Cuándo empieza las vacaciones [Nombre Apellido]?<br>" .
            "📌 ¿Quiénes están ausentes hoy?<br><br>" .
            "🔎 Escribí por ejemplo: <b>vino hoy Juan Perez</b> o <b>vacaciones de Laura Pérez</b>";
    }



    // Empleados ausentes hoy
    // Empleados ausentes hoy (incluye ausentes, licencia y sin registrar)
    elseif (strpos($mensaje, 'ausentes hoy') !== false) {
        $fecha = date('Y-m-d');
        $query = "
        SELECT e.nombre, e.apellido, ea.nombreEstado
        FROM empleado e
        INNER JOIN asistencia a ON e.idempleado = a.idEmpleado
        INNER JOIN estadoasistencia ea ON a.idEstado = ea.idEstado
        WHERE a.fecha = ? AND a.idEstado IN (2,4,7)
    ";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, 's', $fecha);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $nombre, $apellido, $estado);

        $grupos = [
            'Ausentes' => [],
            'Licencia' => [],
            'Sin registrar' => []
        ];

        while (mysqli_stmt_fetch($stmt)) {
            if ($estado === 'Ausente') {
                $grupos['Ausentes'][] = "$nombre $apellido";
            } elseif ($estado === 'Licencia') {
                $grupos['Licencia'][] = "$nombre $apellido";
            } elseif ($estado === 'Sin registrar') {
                $grupos['Sin registrar'][] = "$nombre $apellido";
            }
        }
        mysqli_stmt_close($stmt);

        $respuesta = "Situación de hoy:\n";
        foreach ($grupos as $tipo => $empleados) {
            $respuesta .= $tipo . ": " . (count($empleados) ? implode(", ", $empleados) : "Ninguno") . "\n";
        }
    }


    echo $respuesta;
}
