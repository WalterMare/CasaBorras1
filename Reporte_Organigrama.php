<?php
session_start();

// Validar sesión
if (empty($_SESSION['Usuario_Nombre'])) {
    echo "Sesión expirada. Redirigiendo al inicio de sesión...";
    header('Refresh: 3; URL=cerrarsesion.php');
    exit;
}

// Conexión a la base de datos (ajusta los parámetros)
$conn = new PDO("mysql:host=localhost;dbname=recursoshumanos", "root", "12345");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once 'consulta_OrganigramaEstructural.php';


$organigramaService = new OrganigramaService();
$empleados = $organigramaService->obtenerEmpleadosActivosConJerarquia();
list($jerarquia, $jefes) = $organigramaService->generarJerarquia($empleados);

$estadisticas = $organigramaService->obtenerEstadisticasEstructura();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Organigrama</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/save-svg-as-png/1.4.17/saveSvgAsPng.min.js"></script>
    <style>
        .node rect {
            fill: #fff;
            stroke: #4a7b9d;
            stroke-width: 2px;
        }

        .node.jefe rect {
            fill: #d4e6f7;
        }

        .node text {
            font: 12px sans-serif;
        }

        .link {
            fill: none;
            stroke: #ccc;
            stroke-width: 1.5px;
        }

        #chart {
            width: 100%;
            height: 100vh;
            overflow: auto;
        }
    </style>
</head>

<body>
    <!-- ======= Header ======= -->
    <?php include_once 'partes/header.php'; ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php include_once 'partes/menu.php'; ?>
    <!-- End Sidebar -->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Reporte Estructural</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">Gestor de reportes</li>
                    <li class="breadcrumb-item active">Reporte de Estructura de Personal</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">


                            <div id="chart"></div>

                            <script>
                                const empleados = <?= json_encode($empleados) ?>;
                                const jerarquia = <?= json_encode($jerarquia) ?>;

                                // Construir estructura jerárquica para D3
                                function buildHierarchy(data) {
                                    const rootNodes = data.filter(emp => !emp.nombre_jefe);
                                    const root = {
                                        name: "Organigrama Estructural",
                                        children: []
                                    };

                                    function processNode(emp) {
                                        const node = {
                                            name: emp.empleado,
                                            cargo: emp.cargo,
                                            isJefe: jerarquia[emp.empleado] && jerarquia[emp.empleado].length > 0
                                        };

                                        if (jerarquia[emp.empleado]) {
                                            node.children = jerarquia[emp.empleado].map(sub => processNode(sub));
                                        }

                                        return node;
                                    }

                                    root.children = rootNodes.map(processNode);
                                    return root;
                                }

                                const data = buildHierarchy(empleados);

                                // Configuración del gráfico
                                const margin = {
                                    top: 40,
                                    right: 120,
                                    bottom: 40,
                                    left: 120
                                };
                                const width = 1200 - margin.left - margin.right;
                                const height = 800 - margin.top - margin.bottom;

                                const svg = d3.select("#chart").append("svg")
                                    .attr("width", width + margin.left + margin.right)
                                    .attr("height", height + margin.top + margin.bottom)
                                    .append("g")
                                    .attr("transform", `translate(${margin.left},${margin.top})`);

                                // Crear layout de árbol horizontal
                                const treeLayout = d3.tree().size([height, width]);
                                const root = d3.hierarchy(data);
                                treeLayout(root);

                                // Dibujar conexiones
                                svg.selectAll(".link")
                                    .data(root.links())
                                    .enter().append("path")
                                    .attr("class", "link")
                                    .attr("d", d3.linkHorizontal()
                                        .x(d => d.y)
                                        .y(d => d.x));

                                // Crear grupos de nodos
                                const node = svg.selectAll(".node")
                                    .data(root.descendants())
                                    .enter().append("g")
                                    .attr("class", d => "node" + (d.data.isJefe ? " jefe" : ""))
                                    .attr("transform", d => `translate(${d.y},${d.x})`);

                                // Añadir rectángulos
                                node.append("rect")
                                    .attr("width", 160)
                                    .attr("height", 60)
                                    .attr("x", -80)
                                    .attr("y", -30);

                                // Añadir texto
                                node.append("text")
                                    .attr("dy", ".35em")
                                    .attr("text-anchor", "middle")
                                    .text(d => d.data.name)
                                    .filter(d => d.depth > 0)
                                    .append("tspan")
                                    .attr("x", 0)
                                    .attr("dy", "1.2em")
                                    .text(d => d.data.cargo);
                            </script>
                            <div class="text-center">
                                <button class="btn btn-primary" onclick="exportarOrganigrama()"> Exportar</button>
                                <a href="index.php" class="text-primary fw-bold">Volver al panel</a>
                            </div>
                            <script>
                                function exportarOrganigrama() {
                                    const svg = document.querySelector("svg");

                                    const hoy = new Date();
                                    const fechaStr = hoy.toISOString().slice(0, 10); // "YYYY-MM-DD"

                                    const nombreArchivo = 'organigrama_${fechaStr}.png';

                                    saveSvgAsPng(svg, nombreArchivo, {
                                        scale: 2
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- ======= Footer ======= -->
    <?php include_once 'partes/footer.php'; ?>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
</body>

</html>