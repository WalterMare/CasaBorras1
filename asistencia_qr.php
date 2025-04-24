<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escanear Código QR</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            text-align: center;
            background: #f8f9fa;
        }
        #qr-reader {
            width: 300px;
            margin: 2rem auto;
        }
    </style>
</head>
<body>
    <h2>Escanear Código QR</h2>
    <form id="qr-form" method="POST" action="controlador_asistencia.php">
        <input type="hidden" name="qrData" id="qrData">
    </form>

    <script>
        function escanearQR() {
            const idEmpleado = prompt("Ingrese el ID del empleado para simular escaneo QR:");
            if (idEmpleado) {
                document.getElementById("qrData").value = idEmpleado;
                document.getElementById("qr-form").submit();
            }
        }
    </script>

</body>
</html>
