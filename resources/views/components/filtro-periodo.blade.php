{{--
    Filtro de período (faturamento e vendas): select de períodos prontos que aplica
    na hora; "Personalizado" mostra os campos De/Até ao lado. "Limpar filtro" aparece
    fora do padrão (hoje) e volta para $limpar.
    O fieldset desligado tira de/ate do envio quando o período é pronto.
    O slot recebe campos escondidos de outros filtros da tela (ex.: situação).
    Embaixo, o período aplicado por extenso: confirma o que está na tela sem ler os
    campos. Ele imprime (o relatório impresso precisa dizer de quando é); o resto não.
--}}
@props(['action', 'limpar', 'periodo', 'de', 'ate'])

@php
    $opcoes = \App\Http\Controllers\Controller::PERIODOS;
    $altura = 'min-h-[3.36rem] py-2.5'; // mesma altura do select e dos campos De/Até

    $br = fn ($dia) => \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y');
    $datas = $de === $ate ? $br($de) : 'de '.$br($de).' até '.$br($ate);
    $resumo = $periodo === 'personalizado' ? $datas : $opcoes[$periodo].' — '.$datas;
@endphp

<div {{ $attributes }}>
    <form action="{{ $action }}" method="GET" x-data="{ periodo: @js($periodo) }"
          class="nao-imprimir flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
        {{ $slot }}

        <div>
            <label for="periodo" class="block text-lg font-bold text-ink mb-1">Período</label>
            <x-select id="periodo" name="periodo" x-model="periodo" envolve="sm:w-56"
                      @change="if (periodo !== 'personalizado') $nextTick(() => $el.form.requestSubmit())"
                      class="min-h-[3.36rem] w-full text-lg">
                @foreach ($opcoes as $valor => $nome)
                    <option value="{{ $valor }}" @selected($periodo === $valor)>{{ $nome }}</option>
                @endforeach
            </x-select>
        </div>

        <fieldset x-show="periodo === 'personalizado'" x-cloak
                  :disabled="periodo !== 'personalizado'"
                  class="flex animate-entra flex-col gap-3 sm:flex-row sm:items-end">
            <x-date-range :de="$de" :ate="$ate" />

            <button type="submit" data-rotulo-enviando="Carregando..."
                    class="inline-flex {{ $altura }} cursor-pointer items-center justify-center rounded-lg border-2 border-transparent bg-brand-700 px-6 text-lg font-bold text-white transition-colors hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                Ver período
            </button>
        </fieldset>

        @if ($periodo !== array_key_first($opcoes))
            <a href="{{ $limpar }}"
               class="inline-flex {{ $altura }} animate-entra items-center justify-center rounded-lg border-2 border-gray-400 px-6 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-100">
                Limpar filtro
            </a>
        @endif
    </form>

    <p class="mt-4 text-lg text-ink-muted">
        Período filtrado: <strong class="text-xl text-ink">{{ ucfirst($resumo) }}</strong>
    </p>
</div>
