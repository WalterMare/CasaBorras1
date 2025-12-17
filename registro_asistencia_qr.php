<?php
session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['qrData'])) {
    $qrData = $_POST['qrData'];

    if (preg_match('/empleado:(\d+)/', $qrData, $matches)) {
        $idEmpleado = (int)$matches[1];
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        function obtenerDiaEnEspañol()
        {
            $dias = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];
            return $dias[date('l')];
        }

        $diaSemana = obtenerDiaEnEspañol();
        $horaFin = '';
        // Obtener horario asignado para hoy
        $sql = "SELECT hora_inicio, hora_fin FROM empleado_dia_horario WHERE idempleado = ? AND dia_semana = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $idEmpleado, $diaSemana);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $horaInicio, $horaFin);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Verificar si ya hay asistencia hoy
        $sqlCheck = "SELECT idAsistencia, horaEntrada, horaSalida FROM asistencias WHERE idEmpleado = ? AND fecha = ?";
        $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, 'is', $idEmpleado, $fechaHoy);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_bind_result($stmtCheck, $idAsistencia, $horaEntrada, $horaSalida);
        mysqli_stmt_fetch($stmtCheck);
        mysqli_stmt_close($stmtCheck);

        $horaActualSegundos = strtotime($horaActual);
        $estado = "Presente";
        $observacionEntrada = "";
        $observacionSalida = "";

        if (!empty($horaInicio) && $horaActualSegundos > strtotime($horaInicio)) {
            $tarde = $horaActualSegundos - strtotime($horaInicio);
            $horas = floor($tarde / 3600);
            $minutos = floor(($tarde % 3600) / 60);
            $estado = "Tarde";
            $observacionEntrada = "Llegó tarde: {$horas}h {$minutos}m";
        }

        if ($idAsistencia && empty($horaSalida)) {
            // Registrar salida
            if ($horaActualSegundos < strtotime($horaFin)) {
                $salidaTemprana = strtotime($horaFin) - $horaActualSegundos;
                $hs = floor($salidaTemprana / 3600);
                $min = floor(($salidaTemprana % 3600) / 60);
                $observacionSalida = "Salida antes del horario: {$hs}h {$min}m";
            } else {
                $observacionSalida = "Salida registrada correctamente.";
            }

            $update = "UPDATE asistencias SET horaSalida = ?, observacion_salida = ? WHERE idAsistencia = ?";
            $stmt = mysqli_prepare($conexion, $update);
            mysqli_stmt_bind_param($stmt, 'ssi', $horaActual, $observacionSalida, $idAsistencia);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (!$idAsistencia) {
            // Registrar entrada
            $insert = "INSERT INTO asistencias (idEmpleado, fecha, horaEntrada, estado, observacion_entrada) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $insert);
            mysqli_stmt_bind_param($stmt, 'issss', $idEmpleado, $fechaHoy, $horaActual, $estado, $observacionEntrada);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        header("Location: asistencia.php");
        exit;
    } else {
        echo "Código QR no válido.";
    }
}
