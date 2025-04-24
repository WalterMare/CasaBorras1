<?php
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();


// Si el llamado es por QR (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qrData'])) {
    $idEmpleado = (int) $_POST['qrData'];

    // 🔍 Verificar si el empleado existe y está activo
    $stmt = mysqli_prepare($conexion, "SELECT estado FROM empleado WHERE idempleado = ?");
    mysqli_stmt_bind_param($stmt, 'i', $idEmpleado);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $estadoEmpleado);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$estadoEmpleado) {
        $mensaje = "El empleado no existe o está inactivo.";
        header('Location: asistencia_listado.php?mensaje=' . urlencode($mensaje));
        exit;
    }


    function registrarAsistencia($idEmpleado, $conexion)
    {
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        $diaSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ][date('l')];

        $stmt = mysqli_prepare($conexion, "SELECT hora_inicio, hora_fin FROM empleado_dia_horario WHERE idempleado = ? AND dia_semana = ?");
        mysqli_stmt_bind_param($stmt, 'is', $idEmpleado, $diaSemana);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $horaInicio, $horaFin);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$horaInicio || !$horaFin) {
            return "No hay horario asignado para este día.";
        }

        $stmt = mysqli_prepare($conexion, "SELECT idAsistencia, horaEntrada, horaSalida FROM asistencias WHERE idEmpleado = ? AND fecha = ?");
        mysqli_stmt_bind_param($stmt, 'is', $idEmpleado, $fechaHoy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $idAsistencia, $horaEntrada, $horaSalida);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($idAsistencia && $horaEntrada && !$horaSalida) {
            // Registrar salida
            $observacion = strtotime($horaActual) < strtotime($horaFin) ? "Salida antes del horario asignado" : "";
            $stmt = mysqli_prepare($conexion, "UPDATE asistencias SET horaSalida = ?, observacion_salida = ? WHERE idAsistencia = ?");
            mysqli_stmt_bind_param($stmt, 'ssi', $horaActual, $observacion, $idAsistencia);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return "Salida registrada correctamente.";
        } else {
            // Registrar entrada
            $estado = 'Presente';
            $observacion = strtotime($horaActual) > strtotime($horaInicio) ? "Llegada fuera del horario asignado" : "";
            $stmt = mysqli_prepare($conexion, "INSERT INTO asistencias (idEmpleado, fecha, horaEntrada, estado, observacion_entrada) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'issss', $idEmpleado, $fechaHoy, $horaActual, $estado, $observacion);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return "Entrada registrada correctamente.";
        }
    }


    $mensaje = registrarAsistencia($idEmpleado, $conexion);
    header('Location: asistencia_listado.php?mensaje=' . urlencode($mensaje));
    exit;
}
