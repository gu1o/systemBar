@props(['padding' => 'p-6'])

{{-- O cartão branco do app. Eram 9 cópias literais de
     "bg-white overflow-hidden shadow-xl sm:rounded-lg p-6|p-8" (§V4).
     overflow-clip e não hidden: recorta igual, mas não quebra o sticky de dentro (barra do <x-lote>). --}}
<div {{ $attributes->merge(['class' => "bg-surface overflow-clip border border-edge shadow-sm rounded-card {$padding}"]) }}>
    {{ $slot }}
</div>
