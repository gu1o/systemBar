{{--
    Máscara de telefone. Era o mesmo bloco copiado em customers/create e
    customers/edit, cada um preso a getElementById('phone') (§V4). Aqui vale para
    qualquer campo marcado com data-mask-telefone.
--}}
<script>
    document.querySelectorAll('[data-mask-telefone]').forEach((campo) => {
        campo.addEventListener('input', (e) => {
            const digitos = e.target.value.replace(/\D/g, '');
            let formatado = '';

            if (digitos.length > 0) {
                formatado = '(' + digitos.substring(0, 2);
            }
            if (digitos.length > 2) {
                formatado += ') ' + digitos.substring(2, 7);
            }
            if (digitos.length > 7) {
                formatado += '-' + digitos.substring(7, 11);
            }

            e.target.value = formatado;
        });
    });
</script>
