{{--
    <select> do DaisyUI com a seta que gira ao abrir.
    A seta do .select é background-image e não gira; aqui ela vira um SVG irmão
    que gira com :open (peer-open), só CSS. Navegador sem :open só não anima.
    A seta fica numa faixa branca parada que cobre a ponta direita: texto comprido do
    <select> (appearance: base-select) passa por baixo do padding e ficava atrás da seta.
    Só o SVG gira — girando a faixa junto, no meio do giro ela vazava por cima da borda.
    Foco: sem o outline do DaisyUI; a borda fica na cor da marca.
    Atributos (name, x-model, @change, classes de largura/fonte) vão para o <select>;
    "envolve" vai para a caixa em volta (largura do campo no layout).
--}}
@props(['envolve' => ''])

<div @class(['relative', $envolve])>
    <select {{ $attributes->merge(['class' => 'peer select min-h-11 bg-none pr-8 font-bold focus:outline-none focus:border-brand-700']) }}>
        {{ $slot }}
    </select>
    <span class="pointer-events-none absolute top-px right-px flex h-[calc(100%-2px)] w-8 items-center justify-center rounded-[3.5px] bg-white peer-open:*:rotate-180" aria-hidden="true">
        <svg class="size-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
    </span>
</div>
