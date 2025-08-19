
<?php
function Consultar_licencias_para_reporte_estadistico($conexion, $fechaInicio, $fechaFin)
{
    $query = "
        SELECT 
            IFNULL(tl.descripcion, 'Sin especificar') AS tipo_licencia, 
            COUNT(l.idlicencia) AS total_licencias, 
            IFNULL(SUM(l.cantidaddias), 0) AS total_dias,
            IFNULL(el.nombreEstado, 'Desconocido') AS estado,
            IF(tl.idtipoLicencia IN (1,2,3,4,5,6,7), 1, 0) AS es_pago
        FROM 
            licencia l
        LEFT JOIN 
            tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
        LEFT JOIN 
            estadolicencia el ON l.IdEstado = el.idestadoLicencia
        WHERE 
            l.fechainicio BETWEEN '$fechaInicio' AND '$fechaFin'
        GROUP BY 
            tl.descripcion, el.nombreEstado, es_pago
        ORDER BY 
            tl.descripcion, el.nombreEstado;
    ";

    $result = mysqli_query($conexion, $query);
    $licencias_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $licencias_data[] = $row;
    }
    return $licencias_data;
}
function Consultar_licencias_por_cargo($conexion, $fechaInicio, $fechaFin)
{
    $query = "SELECT 
    c.descripcion AS cargo,
    COUNT(l.idlicencia) AS total_licencias
FROM licencia l
JOIN empleado e ON l.idempleado = e.idempleado
JOIN cargo c ON e.Idcargo = c.idcargo
WHERE l.fechainicio BETWEEN '$fechaInicio' AND '$fechaFin'
GROUP BY c.descripcion
ORDER BY c.descripcion;";

    $result = mysqli_query($conexion, $query);
    $licencias_por_cargo = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $licencias_por_cargo[] = $row;
    }

    return $licencias_por_cargo;
}


function ContarEmpleadosConLicencia($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT COUNT(DISTINCT idEmpleado) AS total_empleados
            FROM licencia
            WHERE fechainicio BETWEEN ? AND ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    return $resultado['total_empleados'] ?? 0;
}

function LicenciasPorMes($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT DATE_FORMAT(fechainicio, '%Y-%m') AS mes, COUNT(*) AS cantidad
            FROM licencia
            WHERE fechainicio BETWEEN ? AND ?
            GROUP BY mes
            ORDER BY mes";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }
    return $datos;
}
function EmpleadosConLicencias($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT 
                e.nombre, 
                e.apellido, 
                c.descripcion AS cargo,
                COUNT(*) AS total_licencias, 
                SUM(l.cantidaddias) AS total_dias
            FROM licencia l
            JOIN empleado e ON e.idempleado = l.idEmpleado
            JOIN cargo c ON c.idcargo = e.Idcargo
            WHERE l.fechainicio BETWEEN ? AND ?
            GROUP BY l.idEmpleado
            ORDER BY total_licencias DESC, total_dias DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $empleados = [];
    while ($fila = $resultado->fetch_assoc()) {
        $empleados[] = $fila;
    }
    return $empleados;
}


function EmpleadoConMasLicencias($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT e.nombre, e.apellido, COUNT(*) AS total_licencias, SUM(l.cantidaddias) AS total_dias
            FROM licencia l
            JOIN empleado e ON e.idempleado = l.idEmpleado
            WHERE l.fechainicio BETWEEN ? AND ?
            GROUP BY l.idEmpleado
            ORDER BY total_licencias DESC, total_dias DESC
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function LicenciasPago($conexion, $fechaInicio, $fechaFin)
{
    $query = "
        SELECT 
            tl.descripcion AS tipo_licencia,
            SUM(l.cantidaddias) AS total_dias
        FROM licencia l
        JOIN tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
        WHERE l.IdEstado IN (1,3)                  -- solo aprobadas
          AND tl.idtipoLicencia IN (1,2,3,4,5,6,7)  -- tipos que cuentan como pago
          AND l.fechainicio <= ?              -- licencia empieza antes o durante el fin del período
          AND l.fechafin >= ?                 -- licencia termina después o durante el inicio del período
        GROUP BY tl.descripcion
        ORDER BY total_dias DESC
    ";

    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    // Bind de parámetros
    $stmt->bind_param('ss', $fechaFin, $fechaInicio);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $licencias_pago = [];
    while ($fila = $resultado->fetch_assoc()) {
        $licencias_pago[] = $fila;
    }

    $stmt->close();
    return $licencias_pago;
}




?>
