{{--
    <select> do DaisyUI com a seta que gira ao abrir.
    A seta do .select é background-image e não gira; aqui ela vira um SVG irmão
    que gira com :open (peer-open), só CSS. Navegador sem :open só não anima.
    Atributos (name, x-model, @change, classes de largura/fonte) vão para o <select>;
    "envolve" vai para a caixa em volta (largura do campo no layout).
--}}
@props(['envolve' => ''])

<div @class(['relative', $envolve])>
    <select {{ $attributes->merge(['class' => 'peer select min-h-11 bg-none pr-8 font-bold']) }}>
        {{ $slot }}
    </select>
    <svg class="pointer-events-none absolute right-2.5 top-1/2 size-4 -translate-y-1/2 transition-transform duration-200 peer-open:rotate-180" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
</div>
