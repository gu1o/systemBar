{{-- Resumo dos erros de validação. Vai no topo de todo formulário: o usuário precisa
     descobrir que algo deu errado sem ter que procurar campo por campo. --}}
@if ($errors->any())
    <div class="mb-6 rounded-lg border-2 border-red-600 bg-red-50 p-4 text-red-800" role="alert" aria-live="assertive"
        tabindex="-1" id="erros-do-formulario">
        <p class="text-lg font-bold">
            {{ $errors->count() === 1 ? 'Corrija o campo abaixo:' : 'Corrija os campos abaixo:' }}
        </p>
        <ul class="mt-2 list-inside list-disc text-base font-medium">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
