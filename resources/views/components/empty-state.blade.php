@props(['acao' => null, 'rotulo' => null])

{{--
    O que aparece quando ainda não há nada cadastrado. Antes os índices usavam
    @foreach, então a primeira tela de um usuário novo era um cabeçalho de tabela
    vazio — nenhuma palavra de orientação no momento de maior risco de desistência (§A8).
--}}
<div class="px-6 py-12 text-center">
    <p class="text-xl text-gray-700">{{ $slot }}</p>

    @if ($acao)
        <a href="{{ $acao }}"
           class="mt-6 inline-flex min-h-11 items-center rounded-lg bg-accent-700 px-8 py-4 text-xl font-bold text-white shadow-md transition-colors hover:bg-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-700">
            {{ $rotulo }}
        </a>
    @endif
</div>
