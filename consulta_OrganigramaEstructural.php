<?php
class OrganigramaService
{
    private $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=recursoshumanos", "root", "12345");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Obtener empleados activos con jerarquía corregida
    public function obtenerEmpleadosActivosConJerarquia()
    {
        $sql = "
            WITH RECURSIVE jerarquia_recursiva AS (
                SELECT 
                    jc.idcargo,
                    jc.idcargo_jefe,
                    0 AS nivel
                FROM jerarquia_cargo jc

                UNION ALL

                SELECT 
                    r.idcargo,
                    j.idcargo_jefe,
                    r.nivel + 1
                FROM jerarquia_recursiva r
                JOIN jerarquia_cargo j ON r.idcargo_jefe = j.idcargo
                WHERE r.idcargo_jefe IS NOT NULL
            ),

            jefes_validos AS (
                SELECT DISTINCT idcargo
                FROM empleado
            ),

            jerarquia_corregida AS (
                SELECT 
                    idcargo,
                    idcargo_jefe
                FROM (
                    SELECT 
                        r.idcargo,
                        r.idcargo_jefe,
                        ROW_NUMBER() OVER (PARTITION BY r.idcargo ORDER BY nivel) AS orden
                    FROM jerarquia_recursiva r
                    JOIN jefes_validos jv ON r.idcargo_jefe = jv.idcargo
                ) sub
                WHERE orden = 1
            )

            SELECT 
                e.idempleado,
                CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                c.descripcion AS cargo,
                CONCAT(j.nombre, ' ', j.apellido) AS nombre_jefe,
                cj.descripcion AS cargo_jefe
            FROM empleado e
            LEFT JOIN jerarquia_corregida jc ON e.idcargo = jc.idcargo
            LEFT JOIN empleado j ON j.idcargo = jc.idcargo_jefe
            LEFT JOIN cargo c ON e.idcargo = c.idcargo
            LEFT JOIN cargo cj ON j.idcargo = cj.idcargo
            WHERE e.fecha_baja IS NULL
            ORDER BY nombre_jefe, empleado
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Construir jerarquía a partir de la lista de empleados
    public function generarJerarquia($empleados)
    {
        $jerarquia = [];
        $jefes = [];

        foreach ($empleados as $empleado) {
            $idJefe = $empleado['nombre_jefe'] ? $empleado['nombre_jefe'] : 'root';

            if (!isset($jerarquia[$idJefe])) {
                $jerarquia[$idJefe] = [];
            }

            $jerarquia[$idJefe][] = $empleado;

            if ($empleado['nombre_jefe']) {
                $jefes[$empleado['nombre_jefe']] = true;
            }
        }

        return [$jerarquia, $jefes];
    }

    // Generar HTML recursivo del organigrama
    public function generarHTML($nodo, $jerarquia, $jefes, $nivel = 0)
    {
        $html = '';
        $margin = $nivel * 180;

        foreach ($nodo as $empleado) {
            $esJefe = isset($jefes[$empleado['empleado']]);
            $claseJefe = $esJefe ? 'es-jefe' : '';

            $html .= '<div class="empleado-container-h">';
            $html .= '<div class="empleado-h ' . $claseJefe . '" style="margin-left: ' . $margin . 'px">';
            $html .= '<div class="nombre-h">' . htmlspecialchars($empleado['empleado']) . '</div>';
            $html .= '<div class="cargo-h">' . htmlspecialchars($empleado['cargo']) . '</div>';
            $html .= '</div>';

            if ($nivel > 0) {
                $html .= '<div class="conector-h" style="left: ' . ($margin - 90) . 'px"></div>';
            }

            if ($esJefe && isset($jerarquia[$empleado['empleado']])) {
                $html .= '<div class="subordinados-h">';
                $html .= $this->generarHTML($jerarquia[$empleado['empleado']], $jerarquia, $jefes, $nivel + 1);
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    // 📊 Estadísticas de estructura organizacional
    public function obtenerEstadisticasEstructura()
    {
        // Nivel más profundo
        $sqlNivel = "
            WITH RECURSIVE jerarquia_recursiva AS (
                SELECT idcargo, idcargo_jefe, 0 AS nivel
                FROM jerarquia_cargo
                UNION ALL
                SELECT jc.idcargo, jc.idcargo_jefe, r.nivel + 1
                FROM jerarquia_recursiva r
                JOIN jerarquia_cargo jc ON r.idcargo_jefe = jc.idcargo
            )
            SELECT MAX(nivel) AS nivel_mas_profundo FROM jerarquia_recursiva
        ";
        $nivelProfundo = $this->conn->query($sqlNivel)->fetch(PDO::FETCH_ASSOC)['nivel_mas_profundo'];

        // Cargos sin subordinados
        $sqlSinSub = "
            SELECT COUNT(*) AS sin_subordinados
            FROM cargo c
            LEFT JOIN jerarquia_cargo jc ON c.idcargo = jc.idcargo_jefe
            WHERE jc.idcargo IS NULL
        ";
        $sinSubordinados = $this->conn->query($sqlSinSub)->fetch(PDO::FETCH_ASSOC)['sin_subordinados'];

        // Cargos sin jefe
        $sqlSinJefe = "
            SELECT COUNT(*) AS sin_jefe
            FROM jerarquia_cargo
            WHERE idcargo_jefe IS NULL
        ";
        $sinJefe = $this->conn->query($sqlSinJefe)->fetch(PDO::FETCH_ASSOC)['sin_jefe'];

        // Nombres de cargos sin subordinados
        $sqlNombresSub = "
            SELECT c.descripcion
            FROM cargo c
            LEFT JOIN jerarquia_cargo jc ON c.idcargo = jc.idcargo_jefe
            WHERE jc.idcargo IS NULL
        ";
        $cargosSinSub = $this->conn->query($sqlNombresSub)->fetchAll(PDO::FETCH_COLUMN);

        // Nombres de cargos sin jefe
        $sqlNombresJefe = "
            SELECT c.descripcion
            FROM cargo c
            JOIN jerarquia_cargo jc ON c.idcargo = jc.idcargo
            WHERE jc.idcargo_jefe IS NULL
        ";
        $cargosSinJefe = $this->conn->query($sqlNombresJefe)->fetchAll(PDO::FETCH_COLUMN);

        return [
            'nivel_mas_profundo' => $nivelProfundo,
            'cantidad_sin_subordinados' => $sinSubordinados,
            'cantidad_sin_jefe' => $sinJefe,
            'cargos_sin_subordinados' => $cargosSinSub,
            'cargos_sin_jefe' => $cargosSinJefe
        ];
    }
}
?>
