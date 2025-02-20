<?php
function ModificarEmpleado($vConexion, $empleado)
{
    // Definir estado
    $default = (empty($_POST['estado']) || $_POST['estado'] == 0) ? 0 : 1;

    // Obtener datos del formulario
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $Idsexo = $_POST["sexo"];
    $fechaNacimiento = $_POST["fechanacimiento"];
    $IdestadoCivil = $_POST["estadocivil"];
    $email = $_POST["email"];
    $estado = $default;
    $fecha_inicio = $_POST["fechainicio"];
    $Idcargo = $_POST["cargo"];
    $direccion = $_POST["direccion"];
    $ciudad = $_POST["ciudad"];
    $tel = $_POST["tel"];
    $idprovincia = $_POST["provincia"];
    $dni = $_POST["documento"];
    $fecha_baja = empty($_POST["fechabaja"]) ? 'NULL' : "'" . $_POST["fechabaja"] . "'";

    // Imagen (solo si se sube una nueva)
    if (isset($_FILES['imagen']) && $_FILES['imagen']['tmp_name']) {
        $imagen = addslashes(file_get_contents($_FILES['imagen']['tmp_name']));
    } else {
        // Mantener la imagen anterior si no se ha subido una nueva
        $imagen = null;
    }

    // Actualizar datos del empleado
    $SQL_Update = "UPDATE empleado 
                   SET nombre='$nombre', apellido='$apellido', Idsexo='$Idsexo', fechaNacimiento='$fechaNacimiento',
                       IdestadoCivil='$IdestadoCivil', email='$email', estado='$estado', fecha_inicio='$fecha_inicio', 
                       Idcargo='$Idcargo', direccion='$direccion', ciudad='$ciudad', tel='$tel', idprovincia='$idprovincia',
                       dni='$dni', fecha_baja=$fecha_baja" . ($imagen ? ", imagen='$imagen'" : "") . "
                   WHERE idempleado=$empleado";

    if (!mysqli_query($vConexion, $SQL_Update)) {
        die('<h4>Error al intentar actualizar el empleado.</h4>');
    }

    // Obtener turno del formulario
    $idturno = intval($_POST['turno']);
    $fecha_asignacion = date('Y-m-d');

    // Verificar si el empleado ya tiene un turno asignado
    $SQL_Verificar = "SELECT * FROM empleado_turno WHERE idempleado = $empleado";
    $resultado = mysqli_query($vConexion, $SQL_Verificar);

    // Si tiene un turno asignado, actualizamos o insertamos el turno
    if (mysqli_num_rows($resultado) > 0) {
        // Si el turno cambia, actualizamos el turno
        $SQL_UpdateTurno = "UPDATE empleado_turno 
                            SET idturno = $idturno, fecha_asignacion = '$fecha_asignacion'
                            WHERE idempleado = $empleado";
        if (!mysqli_query($vConexion, $SQL_UpdateTurno)) {
            die('<h4>Error al actualizar el turno.</h4>');
        }

        // Si el turno cambia, también debemos actualizar los horarios
        $SQL_DeleteHorarios = "DELETE FROM empleado_dia_horario WHERE idempleado = $empleado";
        if (!mysqli_query($vConexion, $SQL_DeleteHorarios)) {
            die('<h4>Error al eliminar los horarios anteriores.</h4>');
        }

        // Insertar los nuevos horarios del turno
        $SQL_Horarios = "SELECT hora_inicio, hora_fin, dia_semana 
                         FROM turno_dia_horario 
                         WHERE idturno = $idturno";
        $resultadoHorarios = mysqli_query($vConexion, $SQL_Horarios);

        while ($row = mysqli_fetch_assoc($resultadoHorarios)) {
            $diaSemana = $row['dia_semana'];
            $horaInicio = $row['hora_inicio'];
            $horaFin = $row['hora_fin'];

            // Insertar los horarios en la tabla empleado_dia_horario
            $SQL_InsertHorarios = "INSERT INTO empleado_dia_horario (idempleado, idturno, dia_semana, hora_inicio, hora_fin) 
                                   VALUES ($empleado, $idturno, '$diaSemana', '$horaInicio', '$horaFin')";
            if (!mysqli_query($vConexion, $SQL_InsertHorarios)) {
                die('<h4>Error al insertar los horarios del turno.</h4>');
            }
        }
    } else {
        // Si no tiene un turno asignado, insertamos uno nuevo
        $SQL_InsertTurno = "INSERT INTO empleado_turno (idempleado, idturno, fecha_asignacion) 
                            VALUES ($empleado, $idturno, '$fecha_asignacion')";
        if (!mysqli_query($vConexion, $SQL_InsertTurno)) {
            die('<h4>Error al asignar el turno.</h4>');
        }

        // Insertar los horarios del nuevo turno
        $SQL_Horarios = "SELECT hora_inicio, hora_fin, dia_semana 
                         FROM turno_dia_horario 
                         WHERE idturno = $idturno";
        $resultadoHorarios = mysqli_query($vConexion, $SQL_Horarios);

        while ($row = mysqli_fetch_assoc($resultadoHorarios)) {
            $diaSemana = $row['dia_semana'];
            $horaInicio = $row['hora_inicio'];
            $horaFin = $row['hora_fin'];

            // Insertar los horarios en la tabla empleado_dia_horario
            $SQL_InsertHorarios = "INSERT INTO empleado_dia_horario (idempleado, idturno, dia_semana, hora_inicio, hora_fin) 
                                   VALUES ($empleado, $idturno, '$diaSemana', '$horaInicio', '$horaFin')";
            if (!mysqli_query($vConexion, $SQL_InsertHorarios)) {
                die('<h4>Error al insertar los horarios del turno.</h4>');
            }
        }
    }

    return true;
}
?>


<?php
function Modificar_EstadoLicencia_Empleado($Id, $Estado, $conexion)
{
    $SQL = "UPDATE empleado AS E SET E.estado=$Estado WHERE E.idempleado=$Id ";

    if (!mysqli_query($conexion, $SQL)) {
        return false;
    }
    return true;
}
?>