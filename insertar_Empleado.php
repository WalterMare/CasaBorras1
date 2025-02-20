<?php 
function InsertarEmpleado($vConexion) {
    $default = 0;
    $variable = $_POST['estado'];
    $default = ($variable == 0 || $variable == null) ? 0 : 1;

    $Imagen = addslashes(file_get_contents($_FILES['imagen']['tmp_name']));

    $SQL_Insert = "INSERT INTO empleado (nombre, apellido, Idsexo, fechaNacimiento, IdestadoCivil, email, estado, fecha_inicio, Idcargo, direccion, ciudad, tel, idprovincia, imagen, dni, fecha_baja) 
    VALUES ('".$_POST['nombre']."', '".$_POST['apellido']."', '".$_POST['sexo']."', '".$_POST['fechanacimiento']."', '".$_POST['estadocivil']."', '".$_POST['email']."', '".$default."', '".$_POST['fechainicio']."', '".$_POST['cargo']."', '".$_POST['direccion']."', '".$_POST['ciudad']."', '".$_POST['tel']."', '".$_POST['provincia']."', '".$Imagen."', '".$_POST['documento']."', '".null."')";

    if (!mysqli_query($vConexion, $SQL_Insert)) {
        die('<h4>Error al intentar insertar el registro.</h4>');
    }
    $idEmpleado = mysqli_insert_id($vConexion);

    // Asignar turno y horarios diarios
    if (!empty($_POST['turno'])) {
        $idTurno = intval($_POST['turno']);
        $fechaAsignacion = date('Y-m-d');

        $SQL_Turno = "INSERT INTO empleado_turno (idempleado, idturno, fecha_asignacion) 
                      VALUES ($idEmpleado, $idTurno, '$fechaAsignacion')";

        if (!mysqli_query($vConexion, $SQL_Turno)) {
            die('<h4>Error al asignar el turno al empleado.</h4>');
        }

        // 🔄 Asignar horarios diarios automáticamente (INCLUYENDO DÍA)
        $SQL_Horarios = "SELECT dia, hora_inicio, hora_fin 
                         FROM turno_dia_horario 
                         WHERE idturno = $idTurno";

        $result = mysqli_query($vConexion, $SQL_Horarios);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dia = $row['dia'];
                $horaInicio = $row['hora_inicio'];
                $horaFin = $row['hora_fin'];

                $SQL_EmpleadoHorario = "INSERT INTO empleado_dia_horario (idempleado, idturno, dia, hora_inicio, hora_fin) 
                                        VALUES ($idEmpleado, $idTurno, '$dia', '$horaInicio', '$horaFin')";

                if (!mysqli_query($vConexion, $SQL_EmpleadoHorario)) {
                    die('<h4>Error al asignar el horario diario al empleado.</h4>');
                }
            }
        } else {
            die('<h4>Error al obtener los horarios diarios del turno.</h4>');
        }
    }

    return true;
}
?>

