
<?php
function Consultar_licencias_para_reporte_estadistico($conexion, $fechaInicio, $fechaFin)
{
    $query = "SELECT 
        IFNULL(tl.descripcion, 'Sin especificar') AS tipo_licencia, 
        COUNT(l.idlicencia) AS total_licencias, 
        IFNULL(SUM(l.cantidaddias), 0) AS total_dias,
        IFNULL(el.nombreEstado, 'Desconocido') AS estado
    FROM 
        licencia l
    LEFT JOIN 
        tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
    LEFT JOIN 
        estadolicencia el ON l.IdEstado = el.idestadoLicencia
    WHERE 
        l.fechainicio BETWEEN '$fechaInicio' AND '$fechaFin'
    GROUP BY 
        tl.descripcion, el.nombreEstado
    ORDER BY 
        tl.descripcion, el.nombreEstado;";

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
                tl.descripcion AS tipo_licencia, 
                el.nombreEstado AS estado,
                COUNT(l.idlicencia) AS total_licencias, 
                SUM(l.cantidaddias) AS total_dias
              FROM 
                licencia l
              JOIN 
                empleado e ON l.idempleado = e.idempleado
              JOIN 
                cargo c ON e.Idcargo = c.idcargo
              JOIN 
                tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
              JOIN 
                estadolicencia el ON l.IdEstado = el.idestadoLicencia
              WHERE 
                l.fechainicio BETWEEN '$fechaInicio' AND '$fechaFin'
              GROUP BY 
                c.descripcion, tl.descripcion, el.nombreEstado
              ORDER BY 
                c.descripcion, tl.descripcion, el.nombreEstado;";

    $result = mysqli_query($conexion, $query);
    $licencias_por_cargo = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $licencias_por_cargo[] = $row;
    }

    return $licencias_por_cargo;
}


function ContarEmpleadosConLicencia($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT COUNT(DISTINCT idEmpleado) AS total_empleados
            FROM licencia
            WHERE fechainicio BETWEEN ? AND ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    return $resultado['total_empleados'] ?? 0;
}

function LicenciasPorMes($conexion, $fechaInicio, $fechaFin) {
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
function EmpleadosConLicencias($conexion, $fechaInicio, $fechaFin) {
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


function EmpleadoConMasLicencias($conexion, $fechaInicio, $fechaFin) {
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





?>
