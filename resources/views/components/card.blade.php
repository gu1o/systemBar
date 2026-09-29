@props(['padding' => 'p-6'])

{{-- O cartão branco do app. Eram 9 cópias literais de
     "bg-white overflow-hidden shadow-xl sm:rounded-lg p-6|p-8" (§V4). --}}
<div {{ $attributes->merge(['class' => "bg-surface overflow-hidden border border-edge shadow-sm rounded-card {$padding}"]) }}>
    {{ $slot }}
</div>
