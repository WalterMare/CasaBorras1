$.ajax({
    type: "GET",
    url: "/recuperarDocumento",
    data: { id: [documento] },
    success: function(data) {
        blob = new Blob([data], { type: "application/pdf" });

        var documentoInput = document.getElementById("documento");
        documentoInput.src = URL.createObjectURL(blob);
    }
})