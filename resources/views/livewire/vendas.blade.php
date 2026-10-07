@php
    $opcoes = \App\Http\Controllers\Controller::PERIODOS;

    $br = fn ($dia) => \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y');
    $datas = $inicio === $fim ? $br($inicio) : 'de '.$br($inicio).' até '.$br($fim);
    $resumo = $periodo === 'personalizado' ? $datas : $opcoes[$periodo].' — '.$datas;
@endphp

{{-- Estado do bloco de datas no Alpine: abre e fecha no clique, sem esperar o servidor. --}}
<div x-data="{ personalizado: @js($periodo === 'personalizado') }">
    <div class="nao-imprimir border-b border-edge px-4 pt-5 sm:px-7 sm:pt-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-ink">Movimentações</h2>
                <p class="mt-1 text-base text-ink-muted">Use as abas para filtrar por situação.</p>
            </div>

            {{-- §F3 — "quem me deve" é o relatório que este negócio mais usa.
                 Continuam links de verdade (abrem em nova aba, funcionam sem JS); o clique
                 normal só troca a lista. --}}
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filtrar compras por situação">
                @foreach (['' => 'Todas', 'pending' => 'Pendentes', 'paid' => 'Pagas', 'cancelled' => 'Canceladas'] as $valor => $rotulo)
                    @php $ativo = $situacao === $valor; @endphp
                    <a href="{{ route('sales.index', array_filter(['situacao' => $valor, 'busca' => $busca])) }}"
                       wire:click.prevent="$set('situacao', '{{ $valor }}')"
                       @if ($ativo) aria-current="true" @endif
                       class="inline-flex min-h-11 items-center rounded-lg border-2 px-4 text-base font-bold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 {{ $ativo ? 'border-brand-700 bg-brand-700 text-white' : 'border-edge text-ink hover:bg-gray-100' }}">
                        {{ $rotulo }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3 pb-5 sm:flex-row sm:items-end sm:justify-between">
            {{-- Filtra sozinha meio segundo depois de parar de digitar (não a cada letra:
                 a lista pulando enquanto se digita confunde). Enter também busca. --}}
            <form wire:submit="buscar($event.target.busca.value)" role="search" class="w-full sm:max-w-md">
                <label for="busca" class="sr-only">Buscar cliente pelo nome</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7.5"/><path d="m16.5 16.5 4 4"/></svg>
                    <input type="search" name="busca" id="busca" wire:model.live.debounce.500ms="busca"
                           placeholder="Buscar pelo nome do cliente"
                           class="min-h-12 w-full rounded-lg border-2 border-edge bg-surface py-2 pl-12 pr-4 text-base placeholder:text-ink-muted focus:border-brand-700 focus:outline-none">
                </div>
            </form>

            <div class="w-full sm:w-64">
                <label for="periodo" class="mb-1.5 block text-sm font-bold text-ink">Período</label>
                <x-select id="periodo" wire:model.live="periodo" @change="personalizado = $event.target.value === 'personalizado'" class="min-h-12 w-full text-base">
                    @foreach ($opcoes as $valor => $nome)
                        <option value="{{ $valor }}">{{ $nome }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </div>

    {{-- Sempre no HTML, mostrado pelo Alpine: com @if o bloco sumia antes de qualquer
         animação de saída. Abre e fecha pela altura (x-collapse) assim que o select
         muda. Altura e não transform: transform em ancestral
         prende o painel fixed do calendário (ver app.css).
         wire:ignore.self: a resposta do Livewire reescrevia o style do form e desfazia
         o x-show (o bloco não fechava); o conteúdo de dentro continua atualizando. --}}
    <form wire:ignore.self x-show="personalizado" x-collapse.duration.300ms @style(["display: none" => $periodo !== 'personalizado'])
          wire:submit="aplicarPeriodo($event.target.de.value, $event.target.ate.value)"
          aria-label="Escolher datas personalizadas"
          class="nao-imprimir border-b border-edge bg-gray-50">
        <div class="animate-entra px-4 py-5 sm:px-7">
            <p class="mb-3 text-base font-bold text-ink">Escolha as datas</p>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- wire:key com as datas: período trocado recria o calendário com os
                     valores novos em vez de manter os antigos no Alpine. --}}
                <div wire:key="datas-{{ $de }}-{{ $ate }}">
                    <x-date-range :de="$de" :ate="$ate" />
                </div>

                <button type="submit"
                        class="inline-flex min-h-12 cursor-pointer items-center justify-center rounded-lg bg-brand-700 px-5 text-base font-bold text-white transition-colors hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    Aplicar datas
                </button>

                <button type="button" wire:click="limparPeriodo" @click="personalizado = false"
                        class="inline-flex min-h-12 cursor-pointer items-center justify-center rounded-lg border-2 border-gray-400 bg-surface px-5 text-base font-bold text-gray-700 transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    Limpar filtro
                </button>
            </div>
        </div>
    </form>

    {{-- Resumo e contagem dizem por extenso o que mudou: quem não viu a tabela
         piscar (ou usa leitor de tela) ainda fica sabendo que a busca rodou.
         O resumo imprime (o relatório impresso precisa dizer de quando é). --}}
    <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7" aria-live="polite">
        <p class="text-base text-ink-muted">Período: <strong class="text-ink">{{ ucfirst($resumo) }}</strong></p>
        <p class="text-base font-semibold text-ink">
            <span wire:loading.remove>{{ $sales->total() }} {{ $sales->total() === 1 ? 'compra encontrada' : 'compras encontradas' }}</span>
            <span wire:loading>Buscando...</span>
        </p>
    </div>

    {{-- No celular as linhas viram cartões: a margem lateral volta só ali. --}}
    <div class="px-4 md:px-0">
    <table wire:loading.class="opacity-50" class="tabela-cartoes w-full border-collapse text-left transition-opacity">
        <thead class="bg-gray-50 text-base font-bold text-gray-700">
            <tr>
                <th scope="col" class="px-6 py-4">Data</th>
                <th scope="col" class="px-6 py-4">Cliente</th>
                <th scope="col" class="px-6 py-4">Total</th>
                <th scope="col" class="px-6 py-4">Situação</th>
                <th scope="col" class="px-6 py-4 text-right">Detalhes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-edge">
            @forelse ($sales as $sale)
                <tr wire:key="venda-{{ $sale->id }}" class="align-middle transition-colors hover:bg-gray-50">
                    <td data-rotulo="Data" class="whitespace-nowrap px-6 py-5 text-base text-ink">
                        {{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td data-rotulo="Cliente" class="px-6 py-5 text-lg font-semibold text-ink">
                        {{ $sale->customer?->name ?? 'Cliente removido' }}</td>
                    <td data-rotulo="Total" class="whitespace-nowrap px-6 py-5 text-lg font-bold text-ink">
                        R$ {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                    <td data-rotulo="Status" class="px-6 py-5">
                        {{-- Era um <select onchange="this.form.submit()">: uma seta do teclado ou
                             a roda do mouse marcava a venda como Paga e gravava, sem intenção e
                             sem volta pela interface. Agora é um botão, que só se aperta de
                             propósito (§A6). --}}
                        @if ($sale->status === 'paid')
                            <x-status-badge cor="verde">Pago</x-status-badge>
                        @elseif ($sale->status === 'cancelled')
                            <x-status-badge cor="vermelho">Cancelada</x-status-badge>
                        @else
                            {{-- Desktop: selo e ação lado a lado. Mobile (cartão): empilhados. --}}
                            <div class="flex flex-col items-start gap-2 md:flex-row md:flex-wrap md:items-center md:gap-3">
                                <x-status-badge cor="amarelo">Pendente</x-status-badge>

                                <form action="{{ route('sales.updateStatus', $sale) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="paid">

                                    <button type="submit" data-rotulo-enviando="Registrando pagamento..."
                                        class="inline-flex min-h-12 cursor-pointer items-center whitespace-nowrap rounded-lg border-2 border-green-700 px-4 text-base font-bold text-green-900 transition-colors hover:bg-green-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700"
                                        onclick="return confirm('Marcar como paga a compra de ' + @js($sale->customer?->name ?? 'cliente removido') + ' no valor de R$ {{ number_format($sale->total_amount, 2, ',', '.') }}?')">
                                        Marcar como pago
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                    <td data-rotulo="Ações" class="px-6 py-5">
                        <div class="flex justify-end">
                            <a href="{{ route('sales.show', $sale) }}"
                                class="inline-flex min-h-12 items-center whitespace-nowrap rounded-lg border-2 border-brand-700 px-5 text-base font-bold text-brand-700 transition-colors hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                                Ver detalhes
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        {{-- A lista é sempre de um período; só consulta "já vendeu alguma vez?" quando vem vazia. --}}
                        @if (! auth()->user()->sales()->exists())
                            <x-empty-state :acao="route('sales.create')" rotulo="Registrar minha primeira venda">
                                Nenhuma compra registrada ainda. Cada venda registrada aqui baixa o estoque sozinha.
                            </x-empty-state>
                        @elseif ($situacao || $busca !== '' || $periodo !== 'hoje')
                            <x-empty-state :acao="route('sales.index', ['periodo' => 'hoje'])" rotulo="Limpar filtros">
                                Nenhuma compra com esse filtro.
                            </x-empty-state>
                        @else
                            <x-empty-state :acao="route('sales.create')" rotulo="Registrar uma venda">
                                Nenhuma compra hoje.
                            </x-empty-state>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="px-4 pb-6 sm:px-7">
        {{ $sales->links('vendor.pagination.tailwind', ['livewire' => true]) }}
    </div>
</div>
