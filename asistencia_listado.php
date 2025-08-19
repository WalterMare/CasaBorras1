<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: cerrarsesion.php');
    exit;
}
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once 'conexiondb.php';
try {
    $conexion = ConexionBD();
} catch (Exception $e) {
    die('Error en la conexión: ' . $e->getMessage());
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 📌 Capturar filtro de apellido (si existe)
$filtroApellido = isset($_GET['apellido']) ? trim($_GET['apellido']) : "";

// Definir cuántos registros mostrar por página
$registrosPorPagina = 10;

// Página actual (por GET), si no existe, es la 1
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Calcular el OFFSET
$offset = ($paginaActual - 1) * $registrosPorPagina;

$sqlTotal = "SELECT COUNT(*) as total FROM empleado WHERE estado = 1";
$totalRegistros = $conexion->query($sqlTotal)->fetch_assoc()['total'];

// Calcular total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);


$fechaHoy = date('Y-m-d');
$diasSemana = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];
$diaSemanaIngles = date('l');
$diaSemana = $diasSemana[$diaSemanaIngles];

// Función para crear o actualizar asistencia y detalle_asistencia
function guardarAsistenciaEstado($conexion, $idEmpleado, $idEstado, $fechaHoy)
{
    $idAsistencia = null;
    $estadoEmpleado = null;

    // Obtener estado del empleado
    $stmt = $conexion->prepare("SELECT estado FROM empleado WHERE idempleado = ?");
    $stmt->bind_param("i", $idEmpleado);
    $stmt->execute();
    $stmt->bind_result($estadoEmpleado);
    $stmt->fetch();
    $stmt->close();

    if ($estadoEmpleado == 0) {
        // Buscar idEstado para 'Licencia'
        $stmt = $conexion->prepare("SELECT idEstado FROM estadoasistencia WHERE nombreEstado = 'Licencia'");
        $stmt->execute();
        $res = $stmt->get_result();
        if ($rowEstado = $res->fetch_assoc()) {
            $idEstadoLicencia = $rowEstado['idEstado'];
        } else {
            die("Estado 'Licencia' no encontrado en la base de datos.");
        }
        $stmt->close();

        // Buscar asistencia existente para licencia
        $stmt = $conexion->prepare("SELECT idAsistencia FROM asistencia WHERE idEmpleado = ? AND fecha = ?");
        $stmt->bind_param("is", $idEmpleado, $fechaHoy);
        $stmt->execute();
        $stmt->bind_result($idAsistencia);
        $stmt->fetch();
        $stmt->close();

        if ($idAsistencia) {
            // Actualizar estado a Licencia

            $stmt = $conexion->prepare("UPDATE asistencia SET idEstado = ? WHERE idAsistencia = ?");
            $stmt->bind_param("ii", $idEstadoLicencia, $idAsistencia);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insertar asistencia con estado Licencia

            $stmt = $conexion->prepare("INSERT INTO asistencia (idEmpleado, fecha, idEstado) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $idEmpleado, $fechaHoy, $idEstadoLicencia);
            $stmt->execute();
            $idAsistencia = $stmt->insert_id;
            $stmt->close();

            // Insertar detalle_asistencia con valores por defecto
            $stmt = $conexion->prepare("INSERT INTO detalle_asistencia (idAsistencia, horasTrabajadas, observaciones) VALUES (?, '00:00:00', '')");
            $stmt->bind_param("i", $idAsistencia);
            $stmt->execute();
            $stmt->close();
        }

        return $idAsistencia;
    } else {
        // Caso normal para otros estados

        // Buscar asistencia existente
        $stmt = $conexion->prepare("SELECT idAsistencia FROM asistencia WHERE idEmpleado = ? AND fecha = ?");
        $stmt->bind_param("is", $idEmpleado, $fechaHoy);
        $stmt->execute();
        $stmt->bind_result($idAsistencia);
        $stmt->fetch();
        $stmt->close();

        if ($idAsistencia) {
            // Actualizar estado
            $stmt = $conexion->prepare("UPDATE asistencia SET idEstado = ? WHERE idAsistencia = ?");
            $stmt->bind_param("ii", $idEstado, $idAsistencia);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insertar asistencia
            $stmt = $conexion->prepare("INSERT INTO asistencia (idEmpleado, fecha, idEstado) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $idEmpleado, $fechaHoy, $idEstado);
            $stmt->execute();
            $idAsistencia = $stmt->insert_id;
            $stmt->close();

            // Insertar detalle_asistencia con valores por defecto
            $stmt = $conexion->prepare("INSERT INTO detalle_asistencia (idAsistencia, horasTrabajadas, observaciones) VALUES (?, '00:00:00', '')");
            $stmt->bind_param("i", $idAsistencia);
            $stmt->execute();
            $stmt->close();
        }

        return $idAsistencia;
    }
}


function obtenerIdEstado($nombre, $conexion)
{
    $stmt = $conexion->prepare("SELECT idEstado FROM estadoasistencia WHERE nombreEstado = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $res = $stmt->get_result();
    $idEstado = null;
    if ($row = $res->fetch_assoc()) {
        $idEstado = $row['idEstado'];
    }
    $stmt->close();
    return $idEstado;
}

// Función para registrar evento asistencia (Entrada o Salida)
function registrarEventoAsistencia($conexion, $idAsistencia, $tipoEvento, $horaEsperadaEntrada, $horaEsperadaSalida)
{
    $idDetalleAsistencia = null;
    $idEventoExistente = null;
    $horaEntradaReal = null;
    $horaSalidaReal = null;

    // Obtener idDetalleAsistencia
    $stmt = $conexion->prepare("SELECT idDetalleAsistencia FROM detalle_asistencia WHERE idAsistencia = ?");
    $stmt->bind_param("i", $idAsistencia);
    $stmt->execute();
    $stmt->bind_result($idDetalleAsistencia);
    $stmt->fetch();
    $stmt->close();

    if (!$idDetalleAsistencia) {
        // Si no existe detalle_asistencia, crearla
        $stmt = $conexion->prepare("INSERT INTO detalle_asistencia (idAsistencia, horasTrabajadas, observaciones) VALUES (?, '00:00:00', '')");
        $stmt->bind_param("i", $idAsistencia);
        $stmt->execute();
        $idDetalleAsistencia = $stmt->insert_id;
        $stmt->close();
    }

    // Verificar si ya existe evento para ese tipo y detalle, para evitar duplicados
    $stmt = $conexion->prepare("SELECT idEvento FROM evento_asistencia WHERE idDetalleAsistencia = ? AND tipoEvento = ?");
    $stmt->bind_param("is", $idDetalleAsistencia, $tipoEvento);
    $stmt->execute();
    $stmt->bind_result($idEventoExistente);
    $existeEvento = $stmt->fetch();
    $stmt->close();

    $horaEvento = date('H:i:s');
    $observacion = '';

    if ($existeEvento) {
        // Actualizar horaEvento si ya existe
        $stmt = $conexion->prepare("UPDATE evento_asistencia SET horaEvento = ?, observacion = ? WHERE idEvento = ?");
        $stmt->bind_param("ssi", $horaEvento, $observacion, $idEventoExistente);
        $stmt->execute();
        $stmt->close();
        $idEvento = $idEventoExistente;
    } else {
        // Insertar nuevo evento
        $stmt = $conexion->prepare("INSERT INTO evento_asistencia (idDetalleAsistencia, tipoEvento, horaEvento, observacion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $idDetalleAsistencia, $tipoEvento, $horaEvento, $observacion);
        $stmt->execute();
        $idEvento = $stmt->insert_id;  // <--- Aquí capturás el id del nuevo evento
        $stmt->close();
    }

    // Ahora obtenemos horaEntrada y horaSalida para generar observación
    $stmt = $conexion->prepare("SELECT 
        MAX(CASE WHEN tipoEvento = 'Entrada' THEN horaEvento END) AS horaEntrada,
        MAX(CASE WHEN tipoEvento = 'Salida' THEN horaEvento END) AS horaSalida
        FROM evento_asistencia WHERE idDetalleAsistencia = ?");
    $stmt->bind_param("i", $idDetalleAsistencia);
    $stmt->execute();
    $stmt->bind_result($horaEntradaReal, $horaSalidaReal);
    $stmt->fetch();
    $stmt->close();

    // Generar observación
    $observacion = generarObservacion($horaEsperadaEntrada, $horaEsperadaSalida, $horaEntradaReal, $horaSalidaReal);

    // Actualizar observación en evento actual
    $stmt = $conexion->prepare("UPDATE evento_asistencia SET observacion = ? WHERE idEvento = ?");
    $stmt->bind_param("si", $observacion, $idEvento);
    $stmt->execute();
    $stmt->close();

    // También podés actualizar detalle_asistencia.observaciones si querés un resumen diario
    $stmt = $conexion->prepare("UPDATE detalle_asistencia SET observaciones = ? WHERE idDetalleAsistencia = ?");
    $stmt->bind_param("si", $observacion, $idDetalleAsistencia);
    $stmt->execute();
    $stmt->close();
    // Retornar idDetalleAsistencia para calcular horas trabajadas
    return $idDetalleAsistencia;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEmpleado = intval($_POST['idEmpleado']);
    $accion = $_POST['accion']; // Puede ser: Presente, Ausente, Justificado, Entrada, Salida

    // Obtener idEstado para estados (no para entrada/salida)
    if (in_array($accion, ['Presente', 'Ausente', 'Justificado'])) {
        $stmt = $conexion->prepare("SELECT idEstado FROM estadoasistencia WHERE nombreEstado = ?");
        $stmt->bind_param("s", $accion);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $idEstado = $row['idEstado'];
        } else {
            die("Estado no válido");
        }
        $stmt->close();

        // Guardar estado y obtener idAsistencia
        $idAsistencia = guardarAsistenciaEstado($conexion, $idEmpleado, $idEstado, $fechaHoy);
        // Si el estado NO es "Presente", limpiar eventos Entrada/Salida y observaciones
        $idEstadoPresente = obtenerIdEstado('Presente', $conexion);
        if ($idEstado !== $idEstadoPresente) {
            if ($idAsistencia) {
                // Eliminar eventos Entrada y Salida
                $stmt = $conexion->prepare("DELETE FROM evento_asistencia WHERE idDetalleAsistencia IN (
                SELECT idDetalleAsistencia FROM detalle_asistencia WHERE idAsistencia = ?
            ) AND (tipoEvento = 'Entrada' OR tipoEvento = 'Salida')");
                $stmt->bind_param("i", $idAsistencia);
                $stmt->execute();
                $stmt->close();

                // Limpiar observaciones y horas trabajadas
                $stmt = $conexion->prepare("UPDATE detalle_asistencia SET observaciones = '', horasTrabajadas = '00:00:00' WHERE idAsistencia = ?");
                $stmt->bind_param("i", $idAsistencia);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    if (in_array($accion, ['Entrada', 'Salida'])) {
        // Para registrar Entrada o Salida, primero asegurar que exista asistencia con estado 'Presente' (o similar)
        // Podrías requerir que el empleado esté marcado Presente para registrar entrada/salida, o crear asistencia si no existe

        // Buscar asistencia para hoy
        $stmt = $conexion->prepare("SELECT idAsistencia FROM asistencia WHERE idEmpleado = ? AND fecha = ?");
        $stmt->bind_param("is", $idEmpleado, $fechaHoy);
        $stmt->execute();
        $stmt->bind_result($idAsistencia);
        $stmt->fetch();
        $stmt->close();

        if (!$idAsistencia) {
            // Crear asistencia con estado Presente por defecto si no existe
            $stmt = $conexion->prepare("SELECT idEstado FROM estadoasistencia WHERE nombreEstado = 'Presente'");
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $idEstadoPresente = $row['idEstado'];
            $stmt->close();

            $idAsistencia = guardarAsistenciaEstado($conexion, $idEmpleado, $idEstadoPresente, $fechaHoy);
        }
        $stmt = $conexion->prepare("
    SELECT 
      COALESCE(edh.hora_inicio, tdh.hora_inicio) AS hora_esperada_entrada,
      COALESCE(edh.hora_fin, tdh.hora_fin) AS hora_esperada_salida
    FROM empleado e
    LEFT JOIN empleado_dia_horario edh ON e.idempleado = edh.idempleado AND edh.dia_semana = ?
    LEFT JOIN empleado_turno et ON e.idempleado = et.idempleado
    LEFT JOIN turno_dia_horario tdh ON et.idturno = tdh.idturno AND tdh.dia_semana = ?
    WHERE e.idempleado = ?
");
        $stmt->bind_param("ssi", $diaSemana, $diaSemana, $idEmpleado);
        $stmt->execute();
        $stmt->bind_result($horaEsperadaEntrada, $horaEsperadaSalida);
        $stmt->fetch();
        $stmt->close();
        // Registrar evento Entrada o Salida y obtener idDetalleAsistencia
        $idDetalleAsistencia = registrarEventoAsistencia($conexion, $idAsistencia, $accion, $horaEsperadaEntrada, $horaEsperadaSalida);

        // Actualizar horas trabajadas
        actualizarHorasTrabajadas($conexion, $idDetalleAsistencia);
    }

    // Redirigir para evitar reenvío
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Consulta para mostrar empleados + asistencia del día y eventos Entrada/Salida
$sql = "
SELECT 
  e.idempleado, e.nombre, e.estado, e.apellido,
  a.idEstado, es.nombreEstado,
  COALESCE(edh.hora_inicio, tdh.hora_inicio) AS hora_esperada_entrada,
  COALESCE(edh.hora_fin, tdh.hora_fin) AS hora_esperada_salida,
  da.idDetalleAsistencia,
  eaEntrada.horaEvento AS horaEntrada,
  eaSalida.horaEvento AS horaSalida,
  da.observaciones AS observacionesDetalle
FROM empleado e
LEFT JOIN asistencia a ON a.idEmpleado = e.idempleado AND a.fecha = ?
LEFT JOIN estadoasistencia es ON a.idEstado = es.idEstado
LEFT JOIN empleado_dia_horario edh ON e.idempleado = edh.idempleado AND edh.dia_semana = ?
LEFT JOIN empleado_turno et ON e.idempleado = et.idempleado
LEFT JOIN turno_dia_horario tdh ON et.idturno = tdh.idturno AND tdh.dia_semana = ?
LEFT JOIN detalle_asistencia da ON da.idAsistencia = a.idAsistencia
LEFT JOIN evento_asistencia eaEntrada ON eaEntrada.idDetalleAsistencia = da.idDetalleAsistencia AND eaEntrada.tipoEvento = 'Entrada'
LEFT JOIN evento_asistencia eaSalida ON eaSalida.idDetalleAsistencia = da.idDetalleAsistencia AND eaSalida.tipoEvento = 'Salida'
WHERE e.estado IN (0,1)
AND e.fecha_baja IS NULL
AND e.apellido LIKE CONCAT('%', ?, '%')
ORDER BY e.apellido, e.nombre
LIMIT ? OFFSET ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssii", $fechaHoy, $diaSemana, $diaSemana, $filtroApellido, $registrosPorPagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();


function actualizarHorasTrabajadas($conexion, $idDetalleAsistencia)
{
    // Obtener horas entrada y salida
    $sqlEventos = "SELECT tipoEvento, horaEvento FROM evento_asistencia WHERE idDetalleAsistencia = ?";
    $stmt = $conexion->prepare($sqlEventos);
    $stmt->bind_param("i", $idDetalleAsistencia);
    $stmt->execute();
    $result = $stmt->get_result();

    $horaEntrada = null;
    $horaSalida = null;

    while ($fila = $result->fetch_assoc()) {
        if ($fila['tipoEvento'] === 'Entrada') {
            $horaEntrada = $fila['horaEvento'];
        } elseif ($fila['tipoEvento'] === 'Salida') {
            $horaSalida = $fila['horaEvento'];
        }
    }
    $stmt->close();

    if ($horaEntrada && $horaSalida) {
        $entrada = new DateTime($horaEntrada);
        $salida = new DateTime($horaSalida);

        // Asegurarse que salida sea mayor que entrada (para casos noche o errores)
        if ($salida < $entrada) {
            // Suponemos salida al día siguiente
            $salida->modify('+1 day');
        }

        $interval = $entrada->diff($salida);
        $horasTrabajadas = $interval->format('%H:%I:%S');

        // Actualizar detalle_asistencia
        $updateSql = "UPDATE detalle_asistencia SET horasTrabajadas = ? WHERE idDetalleAsistencia = ?";
        $updStmt = $conexion->prepare($updateSql);
        $updStmt->bind_param("si", $horasTrabajadas, $idDetalleAsistencia);
        $updStmt->execute();
        $updStmt->close();

        return true;
    }
    return false;
}

function generarObservacion($horaEsperadaEntrada, $horaEsperadaSalida, $horaEntradaReal, $horaSalidaReal)
{
    // Convertir a DateTime para cálculo fácil
    $formato = 'H:i:s';

    $entradaEsperada = DateTime::createFromFormat($formato, $horaEsperadaEntrada);
    $salidaEsperada = DateTime::createFromFormat($formato, $horaEsperadaSalida);
    $entradaReal = $horaEntradaReal ? DateTime::createFromFormat($formato, $horaEntradaReal) : null;
    $salidaReal = $horaSalidaReal ? DateTime::createFromFormat($formato, $horaSalidaReal) : null;

    $observaciones = [];

    if ($entradaReal && $entradaReal > $entradaEsperada) {
        $diff = $entradaEsperada->diff($entradaReal);
        $minutosTarde = $diff->h * 60 + $diff->i;
        $observaciones[] = "Llegó $minutosTarde minutos tarde en la entrada.";
    } elseif ($entradaReal) {
        $observaciones[] = "Ingreso a tiempo.";
    }

    if ($salidaReal && $salidaReal < $salidaEsperada) {
        $diff = $salidaReal->diff($salidaEsperada);
        $minutosAntes = $diff->h * 60 + $diff->i;
        $observaciones[] = "Salió $minutosAntes minutos antes.";
    } elseif ($salidaReal) {
        $observaciones[] = "Salida a tiempo.";
    }

    if (!$entradaReal && !$salidaReal) {
        return "No registró ingreso ni salida.";
    }

    return implode(" ", $observaciones);
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Asistencia de Hoy</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

</head>

<body class="bg-light">

    <h3>Asistencia de Hoy (<?= htmlspecialchars($fechaHoy) ?>)</h3>

    <form method="GET" class="row g-2 align-items-center mb-3">
        <div class="col-auto">
            <input type="text" name="apellido" class="form-control"
                value="<?= htmlspecialchars($filtroApellido) ?>"
                placeholder="Buscar por apellido">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>

    <table class="table table-striped mt-4 small">
        <thead class="table-dark">
            <tr>
                <th>Empleado</th>
                <th>Horario esperado</th>
                <th>Estado actual</th>
                <th>Hora Entrada</th>
                <th>Hora Salida</th>
                <th>Acciones Estado</th>
                <th>Acciones Entrada/Salida</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultado->fetch_assoc()):

                $estadoEmpleado = $row['estado'];
                $nombreEstado = $row['nombreEstado'] ?? 'Sin registrar';

                if ($estadoEmpleado == 0) {
                    $estadoMostrar = 'Licencia';
                } else {
                    $estadoMostrar = $nombreEstado;
                }
                $estadoPresente = ($row['nombreEstado'] ?? '') === 'Presente';
                $estadoAusente = ($row['nombreEstado'] ?? '') === 'Ausente';
                $estadoJustificado = ($row['nombreEstado'] ?? '') === 'Justificado';
                $tieneEntrada = !empty($row['horaEntrada']);
                $tieneSalida = !empty($row['horaSalida']);
            ?>

                <tr>
                    <td style="font-size: 11px"><?= htmlspecialchars($row['apellido'] . " " . $row['nombre']) ?></td>
                    <td style="font-size: 11px"><?= htmlspecialchars($row['hora_esperada_entrada'] . ' - ' . $row['hora_esperada_salida']) ?></td>
                    <td style="font-size: 11px"><?= htmlspecialchars($estadoMostrar) ?></td>
                    <td style="font-size: 11px"><?= htmlspecialchars($row['horaEntrada'] ?? '-') ?></td>
                    <td style="font-size: 11px"><?= htmlspecialchars($row['horaSalida'] ?? '-') ?></td>
                    <?php
                    $trabajaHoy = !empty($row['hora_esperada_entrada']) && !empty($row['hora_esperada_salida']);
                    ?>
                    <td>
                        <?php if ($trabajaHoy): ?>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="idEmpleado" value="<?= $row['idempleado'] ?>">
                                <div class="d-flex gap-1">
                                    <button type="submit" name="accion" value="Presente" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Presente" <?= ($estadoEmpleado == 0 || $estadoPresente || $tieneEntrada || $tieneSalida) ? 'disabled' : '' ?>>
                                        <i class="bi bi-check-circle"></i></button>
                                    <button type="submit" name="accion" value="Ausente" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Ausente" <?= ($estadoEmpleado == 0 || $estadoAusente || $tieneEntrada || $tieneSalida) ? 'disabled' : '' ?>>
                                        <i class="bi bi-x-circle"></i> </button>
                                    <button type="submit" name="accion" value="Justificado" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Justificado" <?= ($estadoEmpleado == 0 || $estadoJustificado || $tieneEntrada || $tieneSalida) ? 'disabled' : '' ?>>
                                        <i class="bi bi-exclamation-circle"></i> </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <span class="text-muted" style="font-size: 11px">Sin acción</span>
                        <?php endif; ?>

                    </td>
                    <?php
                    $esPresente = (isset($row['nombreEstado']) && $row['nombreEstado'] === 'Presente');
                    ?>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="idEmpleado" value="<?= $row['idempleado'] ?>">
                            <button type="submit" name="accion" value="Entrada" class="btn btn-sm btn-primary" style="font-size: 10px" <?= (!$esPresente || isset($row['horaEntrada']) ? 'disabled' : '') ?>>Entrada</button>
                            <button type="submit" name="accion" value="Salida" class="btn btn-sm btn-secondary" style="font-size: 10px" <?= (!$esPresente || isset($row['horaSalida']) ? 'disabled' : '') ?>>Salida</button>
                        </form>
                    </td>
                    <td style="font-size: 11px">
                        <?= htmlspecialchars($row['observacionesDetalle'] ?? '') ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    while ($empleado = $resultado->fetch_assoc()) {
        if ($empleado['estado'] == 0) {
            // Actualizar o crear asistencia con estado Licencia
            guardarAsistenciaEstado($conexion, $empleado['idempleado'], null, $fechaHoy);
        }
    }
    ?>
    <nav>
        <ul class="pagination">
            <?php if ($paginaActual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>">Anterior</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>">Siguiente</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
</body>

</html>