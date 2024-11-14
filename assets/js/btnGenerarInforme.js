document.querySelector('#btnver').addEventListener('click', function(e) {
    e.preventDefault(); // Evitar la recarga de la página
    console.log('Generando Informe');

    // Obtener los valores seleccionados de los filtros
    var grupo = $('input[name="grupo"]:checked').val(); // Valor de "grupo" (1, 3, 5 años)
    var grupo1 = $('input[name="grupo1"]:checked').val(); // Valor de "grupo1" (activo, inactivo, ambos)

    // Validar que se haya seleccionado al menos un filtro
    if (!grupo) {
        alert("Por favor, seleccione un periodo de búsqueda.");
        return;
    }

    // Enviar los datos al servidor con AJAX para generar el PDF
    $.ajax({
        url: 'pdf.php', // Archivo PHP donde procesas la solicitud de PDF
        type: 'POST',
        data: {
            grupo: grupo,
            grupo1: grupo1
        },
        xhrFields: {
            responseType: 'blob' // Esto es importante para tratar la respuesta como un archivo binario (PDF)
        },
        success: function(response) {
            // Creamos un Blob con el tipo adecuado para PDF
            var blob = new Blob([response], { type: 'application/pdf' });

            // Creamos un enlace de descarga para el PDF
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Reporte_Empleados.pdf'; // Nombre del archivo PDF
            link.click(); // Disparamos el clic para descargar el PDF
        },
        error: function(xhr, status, error) {
            console.error("Error al generar el informe:", error);
            alert("Hubo un error al generar el informe.");
        }
    });
});