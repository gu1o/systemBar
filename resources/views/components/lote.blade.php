@props(['rota', 'metodo', 'rotulo', 'enviando', 'aviso' => null])

{{-- Barra de ação em lote sobre uma tabela. Usa o estado do x-data em volta:
     sel (ids marcados, em string — o x-model de checkbox devolve string) e todos
     (ids da página). Cada linha marca com <input type="checkbox" x-model="sel">.
     A seleção vale para a página atual: trocar de página recarrega e zera.
     Com "aviso", o botão pede confirmação no x-modal antes de enviar.
     No celular a barra é sticky: ao rolar a lista ela fica presa no topo da tela
     (até o fim do cartão). O marcador vazio antes dela diz quando ela "soltou":
     passou do topo da tela = presa, ganha sombra. Scroll e não IntersectionObserver:
     o observer só avisa ao cruzar a borda, e um arrasto rápido pula a borda.
     Sticky não pode ganhar wrapper: só gruda dentro do pai. --}}
@php $modal = 'lote-'.md5($rota); @endphp

<div aria-hidden="true"></div>
<div x-data="{ solto: false, medir() { this.solto = this.$el.previousElementSibling.getBoundingClientRect().top < 0 } }"
     x-init="medir()" @scroll.window.passive="medir()"
     :class="solto && 'max-md:border-brand-700 max-md:shadow-lg'"
     class="mb-4 flex flex-col gap-3 rounded-lg border border-edge bg-gray-50 px-4 py-2 transition-shadow max-md:sticky max-md:top-2 max-md:z-20 sm:flex-row sm:items-center sm:justify-between">
    <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 text-lg font-bold text-ink">
        <input type="checkbox" class="size-6 cursor-pointer rounded border-gray-400 text-brand-700 focus:ring-brand-700"
               :checked="sel.length > 0 && sel.length === todos.length"
               x-effect="$el.indeterminate = sel.length > 0 && sel.length < todos.length"
               @change="sel = $event.target.checked ? [...todos] : []">
        <span>Selecionar todos<span class="hidden sm:inline"> desta página</span></span>
    </label>

    {{-- Celular: contador e botão não cabem lado a lado; a contagem vai dentro do
         botão ("Arquivar (2)") e o contador fica só para leitor de tela. --}}
    <div class="flex items-center gap-3 sm:justify-between">
        <span class="whitespace-nowrap text-base text-ink-muted max-sm:sr-only" aria-live="polite"
              x-text="sel.length === 1 ? '1 selecionado' : sel.length + ' selecionados'"></span>

        <button type="{{ $aviso ? 'button' : 'submit' }}" form="{{ $modal }}" :disabled="sel.length === 0"
                @if ($aviso) x-on:click="$dispatch('open-modal', '{{ $modal }}')" @endif
                @unless ($aviso) data-rotulo-enviando="{{ $enviando }}" @endunless
                class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-lg border-2 border-brand-700 px-5 py-2 text-base font-bold text-brand-700 transition-colors hover:bg-brand-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent sm:w-auto">
            <span>{{ $rotulo }}<span class="hidden sm:inline"> selecionados</span><span class="sm:hidden" x-text="' (' + sel.length + ')'"></span></span>
        </button>
    </div>
</div>

<form id="{{ $modal }}" action="{{ $rota }}" method="POST" class="hidden">
    @csrf
    @method($metodo)
    <template x-for="id in sel" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>
</form>

@if ($aviso)
    <x-modal :name="$modal" maxWidth="lg" focusable>
        <div class="p-6 text-left">
            <h2 class="text-2xl font-bold text-gray-900"
                x-text="'{{ $rotulo }} ' + (sel.length === 1 ? '1 item' : sel.length + ' itens') + '?'"></h2>

            <p class="mt-3 text-lg text-gray-700">{{ $aviso }}</p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close')"
                        class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border-2 border-gray-400 px-6 py-3 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-100">
                    Manter
                </button>

                <button type="submit" form="{{ $modal }}" data-rotulo-enviando="{{ $enviando }}"
                        class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg bg-red-700 px-6 py-3 text-lg font-bold text-white transition-colors hover:bg-red-800">
                    {{ $rotulo }}
                </button>
            </div>
        </div>
    </x-modal>
@endif
