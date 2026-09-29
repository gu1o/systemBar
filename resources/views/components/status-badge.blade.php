@props(['cor' => 'verde'])

{{-- Selo de status. Eram 3 cópias, e duas delas com a classe grudada
     ("rounded-fullbg-green-100"), que anulava o fundo inteiro (§V4, §A11). --}}
@php
    $cores = [
        'verde' => 'bg-green-100 text-green-900',
        'vermelho' => 'bg-red-100 text-red-900',
        'amarelo' => 'bg-yellow-100 text-yellow-900',
    ][$cor];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-4 py-2 text-base font-bold {$cores}"]) }}>
    {{ $slot }}
</span>
