
<?php
function Consultar_licencias_para_reporte_estadistico($conexion, $fechaInicio, $fechaFin)
{
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
    LEFT JOIN 
        detallelicencia d ON l.idlicencia = d.idLicencia
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
?>