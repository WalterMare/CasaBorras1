<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escanear Código QR</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        #qr-reader {
            width: 400px;
        }
    </style>
</head>
<body>
    <h2>Escanear Código QR</h2>
    <div id="qr-reader"></div>
    <form id="qr-form" method="POST" action="registro_asistencia_qr.php">
        <input type="hidden" name="qrData" id="qrData">
    </form>

    <script>
        function onScanSuccess(decodedText) {
            document.getElementById("qrData").value = decodedText;
            document.getElementById("qr-form").submit();
        }
        const scanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
        scanner.render(onScanSuccess);
    </script>
</body>
</html>
