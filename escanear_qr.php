<?php
session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $qrData = $_POST['qrData'];

    if (strpos($qrData, 'empleado:') === 0) {
        $idEmpleado = (int) str_replace('empleado:', '', $qrData);
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        $diaSemana = date('l', strtotime($fechaHoy));

        // Obtener horario asignado para hoy
        $queryHorario = "SELECT hora_inicio, hora_fin FROM empleado_dia_horario WHERE idempleado = ? AND dia_semana = ?";
        $stmtHorario = mysqli_prepare($conexion, $queryHorario);
        mysqli_stmt_bind_param($stmtHorario, 'is', $idEmpleado, $diaSemana);
        mysqli_stmt_execute($stmtHorario);
        mysqli_stmt_bind_result($stmtHorario, $horaInicio, $horaFin);
        mysqli_stmt_fetch($stmtHorario);
        mysqli_stmt_close($stmtHorario);

        $horaActualSegundos = strtotime($horaActual);
        $horaInicioSegundos = strtotime($horaInicio);
        $horaFinSegundos = strtotime($horaFin);

        $estado = 'Presente';
        $observaciones = [];

        if ($horaActualSegundos > $horaInicioSegundos) {
            $diferenciaSegundos = $horaActualSegundos - $horaInicioSegundos;
            $diferenciaHoras = floor($diferenciaSegundos / 3600);
            $diferenciaMinutos = floor(($diferenciaSegundos % 3600) / 60);
            $estado = 'Tarde';
            $observaciones[] = "Llegó tarde: $diferenciaHoras horas y $diferenciaMinutos minutos";
        }

        // Verificar asistencia
        $queryAsistencia = "SELECT idAsistencia, horaSalida, observaciones FROM asistencias WHERE idEmpleado = ? AND fecha = ?";
        $stmtAsistencia = mysqli_prepare($conexion, $queryAsistencia);
        mysqli_stmt_bind_param($stmtAsistencia, 'is', $idEmpleado, $fechaHoy);
        mysqli_stmt_execute($stmtAsistencia);
        mysqli_stmt_bind_result($stmtAsistencia, $idAsistencia, $horaSalida, $obsPrevias);
        mysqli_stmt_fetch($stmtAsistencia);
        mysqli_stmt_close($stmtAsistencia);

        if ($idAsistencia) {
            if ($horaSalida === NULL) {
                $updateSalida = "UPDATE asistencias SET horaSalida = ?, observaciones = ? WHERE idAsistencia = ?";
                if ($horaActualSegundos < $horaFinSegundos) {
                    $diferenciaSegundos = $horaFinSegundos - $horaActualSegundos;
                    $diferenciaHoras = floor($diferenciaSegundos / 3600);
                    $diferenciaMinutos = floor(($diferenciaSegundos % 3600) / 60);
                    $observaciones[] = "Salida anticipada: $diferenciaHoras horas y $diferenciaMinutos minutos";
                }
                $observacionesTexto = implode(' | ', array_filter([$obsPrevias, ...$observaciones]));

                $stmtSalida = mysqli_prepare($conexion, $updateSalida);
                mysqli_stmt_bind_param($stmtSalida, 'ssi', $horaActual, $observacionesTexto, $idAsistencia);
                mysqli_stmt_execute($stmtSalida);
                mysqli_stmt_close($stmtSalida);
            }
        } else {
            $observacionesTexto = implode(' | ', $observaciones);
            $insertAsistencia = "INSERT INTO asistencias (idEmpleado, fecha, horaEntrada, estado, observaciones) VALUES (?, ?, ?, ?, ?)";
            $stmtEntrada = mysqli_prepare($conexion, $insertAsistencia);
            mysqli_stmt_bind_param($stmtEntrada, 'issss', $idEmpleado, $fechaHoy, $horaActual, $estado, $observacionesTexto);
            mysqli_stmt_execute($stmtEntrada);
            mysqli_stmt_close($stmtEntrada);
        }
        header('Location: asistencia.php');
        exit;
    } else {
        echo "QR no válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Escanear Código QR - Asistencia</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        #qr-reader {
            width: 400px;
        }
    </style>
</head>

<body>
    <h2>Escanear Código QR</h2>
    <div id="qr-reader"></div>
    <form id="qr-form" method="POST">
        <input type="hidden" name="qrData" id="qrData">
    </form>

    <script>
        function onScanSuccess(decodedText) {
            document.getElementById("qrData").value = decodedText;
            document.getElementById("qr-form").submit();
        }
        const scanner = new Html5QrcodeScanner("qr-reader", {
            fps: 10,
            qrbox: 250
        });
        scanner.render(onScanSuccess);
    </script>
</body>

</html>