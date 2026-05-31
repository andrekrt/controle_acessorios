function aplicarMascaraMoeda(campo) {
    campo.addEventListener("input", function () {
        let valor = campo.value;

        valor = valor.replace(/\D/g, "");

        if (valor === "") {
            campo.value = "";
            return;
        }

        valor = (parseInt(valor, 10) / 100).toFixed(2);
        valor = valor.replace(".", ",");

        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        campo.value = "R$ " + valor;
    });
}

document.querySelectorAll(".moeda").forEach(function (campo) {
    aplicarMascaraMoeda(campo);
});