<?php
session_start();

//si tengo vacio mi elemento de sesion me tiene q redireccionar al login.. 
//al cerrarsesion para que mate todo de la sesion y el se encarga de ubicar en el login
if (empty($_SESSION['Usuario_Nombre'])) {
  header('Location: cerrarsesion.php');
  exit;
}
require_once 'conexiondb.php';
$conexion = ConexionBD();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idEmpleado = intval($_POST['idEmpleado']);
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'] ?? '';

    $fecha = $_POST['fecha'];
    $horaEntrada = $_POST['horaEntrada'];

    // Obtener la hora de inicio del turno del empleado
    $consultaTurno = "SELECT T.hora_inicio 
                      FROM empleado_turno ET
                      JOIN turno T ON ET.idturno = T.idturno
                      WHERE ET.idempleado = $idEmpleado AND ET.fecha_asignacion = '$fecha'";
    $resultadoTurno = mysqli_query($conexion, $consultaTurno);
    $turno = mysqli_fetch_assoc($resultadoTurno);

    if ($turno) {
        $horaInicioTurno = $turno['hora_inicio'];
        
        // Convertir las horas a segundos para comparar
        $horaEntradaSegundos = strtotime($horaEntrada);
        $horaInicioSegundos = strtotime($horaInicioTurno);

        // Si la hora de entrada es mayor que la hora de inicio, entonces es tarde
        if ($horaEntradaSegundos > $horaInicioSegundos) {
            // Calcular la diferencia en minutos
            $diferenciaSegundos = $horaEntradaSegundos - $horaInicioSegundos;
            $diferenciaMinutos = round($diferenciaSegundos / 60);  // Convertir segundos a minutos

            // Añadir la observación con la cantidad de minutos de tardanza
            $observaciones = "Llegó tarde: $diferenciaMinutos minutos";
            
        }
    }

    // Verificar si ya hay un registro de asistencia para hoy
    $verificar = "SELECT idAsistencia FROM asistencias WHERE idEmpleado = $idEmpleado AND fecha = '$fecha'";
    $resultado = mysqli_query($conexion, $verificar);

    if (mysqli_num_rows($resultado) > 0) {
        echo "El empleado ya tiene asistencia registrada hoy.";
    } else {
        $insertar = "INSERT INTO asistencias (idEmpleado, fecha, horaEntrada, estado, observaciones) 
                     VALUES ($idEmpleado, '$fecha', '$horaEntrada', '$estado', '$observaciones')";
        if (mysqli_query($conexion, $insertar)) {
            echo "Asistencia registrada correctamente.";
        } else {
            echo "Error al registrar asistencia.";
        }
    }
}
?>

