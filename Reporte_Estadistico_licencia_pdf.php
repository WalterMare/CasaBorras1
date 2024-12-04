<?php
function Consultar_licencias_para_reporte_estadistico($conexion, $fechaInicio, $fechaFin)
{

    // Consulta para obtener el número de licencias por tipo y su estado
    $query = "SELECT 
        tl.descripcion AS tipo_licencia, 
        COUNT(l.idlicencia) AS total_licencias, 
        SUM(l.cantidaddias) AS total_dias,
        el.nombreEstado AS estado
    FROM 
        licencia l
    JOIN 
        tipolicencia tl ON l.IdTipo = tl.idtipoLicencia
    JOIN 
        estadolicencia el ON l.IdEstado = el.idestadoLicencia
    WHERE 
        l.fechainicio BETWEEN '$fechaInicio' AND '$fechaFin'
    GROUP BY 
        tl.descripcion, el.nombreEstado
    ORDER BY 
        tl.descripcion, el.nombreEstado;
";

    // Ejecutar la consulta
    $result = mysqli_query($conexion, $query);

    // Almacenar los resultados para el reporte
    $licencias_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $licencias_data[] = $row;
    }
}
?>