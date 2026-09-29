@props(['titulo'])

{{-- Cabeçalho da página, com espaço para uma ação à direita. Eram 5 cópias (§V4).
     Envolve com flex-wrap: em telas estreitas o botão desce em vez de espremer o título. --}}
<div class="flex flex-wrap items-center justify-between gap-4">
    <h2 class="font-semibold text-xl text-white leading-tight">{{ $titulo }}</h2>

    {{ $slot }}
</div>
