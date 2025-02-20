<?php
session_start();

// Si no hay sesión, redirige al login
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}

require_once 'conexiondb.php';
$conexion = ConexionBD();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del QR
    $qrData = $_POST['qrData'];

    if (strpos($qrData, 'empleado:') === 0) {
        $idEmpleado = intval(str_replace('empleado:', '', $qrData));
        $date = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $fecha = $date->format('Y-m-d');
        $horaActual = $date->format('H:i:s');

        // Verificar el turno del empleado
        $consultaTurno = "SELECT T.hora_inicio, T.hora_fin 
                          FROM turno T 
                          JOIN empleado_turno ET ON T.idturno = ET.idturno 
                          WHERE ET.idempleado = $idEmpleado LIMIT 1";
        $resultadoTurno = mysqli_query($conexion, $consultaTurno);
        $turnoData = mysqli_fetch_assoc($resultadoTurno);

        $estado = 'Presente';
        $observaciones = [];

        if ($turnoData) {
            $horaInicioTurno = $turnoData['hora_inicio'];
            $horaFinTurno = $turnoData['hora_fin'];

            $horaActualSegundos = strtotime($horaActual);
            $horaInicioSegundos = strtotime($horaInicioTurno);
            $horaFinSegundos = strtotime($horaFinTurno);

            if ($horaActualSegundos > $horaInicioSegundos) {
                $diferenciaSegundos = $horaActualSegundos - $horaInicioSegundos;
                $diferenciaHoras = floor($diferenciaSegundos / 3600);
                $diferenciaMinutos = floor(($diferenciaSegundos % 3600) / 60);
                $estado = 'Tarde';
                $observaciones[] = "Llegó tarde: $diferenciaHoras horas y $diferenciaMinutos minutos";
            }
        }

        $consulta = "SELECT idAsistencia, horaSalida, observaciones FROM asistencias 
                     WHERE idEmpleado = $idEmpleado AND fecha = '$fecha' 
                     LIMIT 1";
        $resultado = mysqli_query($conexion, $consulta);
        $fila = mysqli_fetch_assoc($resultado);

        if ($fila) {
            if ($fila['horaSalida'] === NULL) {
                $updateQuery = "UPDATE asistencias 
                                SET horaSalida = '$horaActual' 
                                WHERE idAsistencia = " . $fila['idAsistencia'];

                if (mysqli_query($conexion, $updateQuery)) {
                    echo "Salida registrada correctamente.";
                    
                    if ($horaActualSegundos < $horaFinSegundos) {
                        $diferenciaSegundos = $horaFinSegundos - $horaActualSegundos;
                        $diferenciaHoras = floor($diferenciaSegundos / 3600);
                        $diferenciaMinutos = floor(($diferenciaSegundos % 3600) / 60);
                        $observaciones[] = "Salida anticipada: $diferenciaHoras horas y $diferenciaMinutos minutos";
                    }
                    
                    $observacionesPrevias = !empty($fila['observaciones']) ? [$fila['observaciones']] : [];
                    $observacionesTexto = implode(' | ', array_filter(array_merge($observacionesPrevias, $observaciones)));
                    
                    if (!empty($observacionesTexto)) {
                        $updateObsQuery = "UPDATE asistencias 
                                           SET observaciones = '$observacionesTexto' 
                                           WHERE idAsistencia = " . $fila['idAsistencia'];
                        mysqli_query($conexion, $updateObsQuery);
                    }
                } else {
                    echo "Error al registrar salida.";
                }
            } else {
                echo "La salida ya fue registrada previamente.";
            }
        } else {
            $observacionesTexto = !empty($observaciones) ? implode(' | ', $observaciones) : '';
            $insertQuery = "INSERT INTO asistencias (idEmpleado, fecha, horaEntrada, estado, observaciones) 
                            VALUES ($idEmpleado, '$fecha', '$horaActual', '$estado', '$observacionesTexto')";
            
            if (mysqli_query($conexion, $insertQuery)) {
                echo "Entrada registrada correctamente.";
            } else {
                echo "Error al registrar entrada.";
            }
        }

        header('Location: Asistencia_Empleados.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear Código QR</title>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body {
            width: 400px;
            height: 400px;
            margin-top: 50px; /* Ajusta el valor según lo que necesites */
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
        
        let scanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 100 });
        scanner.render(onScanSuccess);
    </script>
</body>
</html>



