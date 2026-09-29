@props(['type' => 'success'])

{{--
    Mensagem de resultado de uma ação. Antes havia 3 cópias deste bloco com 2
    marcações diferentes, e `session('error')` não era renderizado em lugar
    nenhum do app — erro do servidor simplesmente não chegava ao usuário (§A7).
--}}
@php
    $estilos = [
        'success' => ['borda' => 'border-green-600', 'fundo' => 'bg-green-50', 'texto' => 'text-green-900', 'titulo' => 'Deu certo!'],
        'error' => ['borda' => 'border-red-700', 'fundo' => 'bg-red-50', 'texto' => 'text-red-900', 'titulo' => 'Algo deu errado'],
    ][$type];
@endphp

<div {{ $attributes->merge(['class' => "border-l-8 {$estilos['borda']} {$estilos['fundo']} {$estilos['texto']} p-5 rounded-lg shadow"]) }}
     role="alert" aria-live="polite">
    <p class="text-xl font-bold">{{ $estilos['titulo'] }}</p>
    <p class="text-lg">{{ $slot }}</p>
</div>
