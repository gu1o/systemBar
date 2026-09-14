{{--
    Máscara de moeda pt-BR usada pelos campos de preço.
    Vive aqui porque é usada em products/create e products/edit — quando morava
    dentro de products/create, a tela de edição chamava uma função inexistente e
    dava ReferenceError a cada tecla (§B6).
--}}
<script>
    const brlCurrencyMask = (e) => {
        const {
            value
        } = e.target;
        let mask = "";
        mask = value.replace(",", "").replace(".", "").replace(/\D/g, "");

        const options = {
            minimumFractionDigits: 2
        };
        const result = new Intl.NumberFormat("pt-BR", options).format(
            parseFloat(mask) / 100,
        );

        if (result === "NaN") {
            e.target.value = "";
            return;
        }

        e.target.value = result;
    };
</script>
