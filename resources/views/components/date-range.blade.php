{{--
    Campos "De" e "Até" com o calendário Cally (usado pelo <x-filtro-periodo>).
    Cada campo abre um calendário de um dia só; um limita o outro (De não passa do
    Até, Até não fica antes do De) e datas futuras ficam bloqueadas.
    Mês e ano escolhidos em <select> nativo: no celular abre a roleta do sistema,
    bem mais fácil que tocar na seta dez vezes.
    Envia os campos "de" e "ate" (Y-m-d). Vazio = sem limite naquela ponta.
    No celular abre como painel no rodapé da tela; no computador, no centro.
    Sempre "fixed": o <x-card> tem overflow-hidden e cortaria um painel "absolute".
--}}
@props(['de' => null, 'ate' => null])

@php
    $hoje = today();
    // ponytail: 5 anos para trás fixo; trocar por um prop "desde" se precisar de mais histórico.
    $primeiroAno = $hoje->year - 5;
    $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $minimo = $primeiroAno.'-01-01';
    $maximo = $hoje->toDateString();
@endphp

<div x-data="{
        aberto: false,
        alvo: 'de',
        de: @js($de ?: ''),
        ate: @js($ate ?: ''),
        br: d => d.split('-').reverse().join('/'),
        mes: {{ $hoje->month }},
        ano: {{ $hoje->year }},
        atual: 0,
        {{-- Abre o calendário no mês da data do campo tocado (ou no mês atual). --}}
        abrir(alvo) {
            this.alvo = alvo;
            const dia = this[alvo] || @js($maximo);
            [this.ano, this.mes] = dia.split('-').slice(0, 2).map(Number);
            this.atual = this.ano * 12 + this.mes;
            this.aberto = true;
            this.$nextTick(() => this.$refs.cal.focusedDate = dia);
        },
        {{-- Ano atual: só até o mês corrente. Ajusta o mês antes de navegar.
             Pela propriedade, não pelo atributo: as setas mudam só a propriedade, e
             repetir um valor de atributo já usado (março → seta → março) era ignorado. --}}
        irPara() {
            if (this.ano === {{ $hoje->year }} && this.mes > {{ $hoje->month }}) this.mes = {{ $hoje->month }};
            this.$refs.cal.focusedDate = this.ano + '-' + String(this.mes).padStart(2, '0') + '-01';
            this.animar();
        },
        {{-- Grade entra deslizando do lado para onde se andou (frente: da direita).
             Setas e selects passam por aqui; mexer só o dia dentro do mês não anima. --}}
        animar() {
            const novo = this.ano * 12 + this.mes;
            if (novo === this.atual) return;
            const lado = novo > this.atual ? 1 : -1;
            this.atual = novo;
            if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            this.$refs.cal.querySelector('calendar-month').animate(
                [{ opacity: 0, transform: `translateX(${lado * 1.5}rem)` }, { opacity: 1, transform: 'none' }],
                { duration: 220, easing: 'ease-out' },
            );
        },
     }"
     @keydown.escape="aberto = false"
     class="relative">
    <input type="hidden" name="de" :value="de">
    <input type="hidden" name="ate" :value="ate">

    <div class="flex flex-col gap-3 sm:flex-row">
        @foreach (['de' => 'De', 'ate' => 'Até'] as $chave => $nome)
            <div>
                <span id="data-{{ $chave }}-rotulo" class="block text-lg font-bold text-ink mb-1">{{ $nome }}</span>
                <button type="button" @click="abrir('{{ $chave }}')"
                        aria-labelledby="data-{{ $chave }}-rotulo data-{{ $chave }}-valor" aria-haspopup="dialog" :aria-expanded="aberto && alvo === '{{ $chave }}'"
                        class="flex min-h-11 w-full cursor-pointer items-center gap-3 rounded border border-gray-500 bg-surface px-4 py-3 text-left text-lg sm:min-w-48 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    <svg class="size-6 shrink-0 text-brand-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    <span id="data-{{ $chave }}-valor" x-text="{{ $chave }} ? br({{ $chave }}) : 'Escolher'"></span>
                </button>
            </div>
        @endforeach
    </div>

    {{-- Fundo escuro: deixa claro que o painel está por cima e fecha ao tocar fora. --}}
    <div x-show="aberto" x-cloak x-transition.opacity.duration.300ms @click="aberto = false" class="fixed inset-0 z-30 bg-black/40"></div>

    <div x-show="aberto" x-cloak x-transition:enter="painel-entra" x-transition:leave="painel-sai"
         role="dialog" :aria-label="alvo === 'de' ? 'Escolher data inicial' : 'Escolher data final'"
         class="fixed inset-x-0 bottom-0 z-40 max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-t-card border border-edge bg-surface p-4 shadow-xl sm:inset-x-auto sm:bottom-auto sm:left-1/2 sm:top-1/2 sm:w-[24rem] sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-card">
        <p class="text-base text-ink-muted" x-text="alvo === 'de' ? 'Escolha a data inicial:' : 'Escolha a data final:'"></p>

        <calendar-date class="cally mt-2 w-full" locale="pt-BR" first-day-of-week="0"
                       x-ref="cal"
                       :min="alvo === 'ate' && de ? de : @js($minimo)"
                       :max="alvo === 'de' && ate ? ate : @js($maximo)"
                       :value="alvo === 'de' ? de : ate"
                       @focusday="[ano, mes] = $refs.cal.focusedDate.split('-').slice(0, 2).map(Number); animar()"
                       @change.self="alvo === 'de' ? (de = $event.target.value) : (ate = $event.target.value); aberto = false">
            <svg aria-label="Mês anterior" class="size-6" slot="previous" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            <svg aria-label="Próximo mês" class="size-6" slot="next" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <div slot="heading" class="flex gap-3">
                <x-select x-model.number="mes" @change="irPara()" aria-label="Mês" class="w-auto text-base">
                    @foreach ($meses as $i => $nome)
                        <option value="{{ $i + 1 }}" :disabled="ano === {{ $hoje->year }} && {{ $i + 1 }} > {{ $hoje->month }}">{{ $nome }}</option>
                    @endforeach
                </x-select>
                <x-select x-model.number="ano" @change="irPara()" aria-label="Ano" class="w-auto text-base">
                    @for ($a = $hoje->year; $a >= $primeiroAno; $a--)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endfor
                </x-select>
            </div>
            <calendar-month></calendar-month>
        </calendar-date>

        <button type="button" @click="aberto = false"
                class="mt-3 min-h-11 w-full cursor-pointer rounded-lg border-2 border-gray-400 px-4 py-2 text-lg font-bold text-gray-700 hover:bg-gray-100">
            Fechar
        </button>
    </div>
</div>
