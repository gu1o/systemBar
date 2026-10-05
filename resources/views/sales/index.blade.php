<x-app-layout>
    <x-slot name="titulo">Registro de Compras</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Registro de Compras')">
            <a href="{{ route('sales.create') }}"
                class="bg-brand-700 hover:bg-brand-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Nova Venda
            </a>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card>
                {{-- §F3 — "quem me deve" é o relatório que este negócio mais usa. --}}
                <div class="mb-6 flex flex-wrap gap-3" role="group" aria-label="Filtrar compras por situação">
                    @foreach ([null => 'Todas', 'pending' => 'Pendentes', 'paid' => 'Pagas', 'cancelled' => 'Canceladas'] as $valor => $rotulo)
                        @php $ativo = $situacao === ($valor ?: null); @endphp
                        <a href="{{ route('sales.index', array_filter(['situacao' => $valor])) }}"
                           @if ($ativo) aria-current="true" @endif
                           class="inline-flex min-h-11 items-center rounded-lg border-2 px-5 py-3 text-base font-bold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 {{ $ativo ? 'border-brand-700 bg-brand-700 text-white' : 'border-edge text-ink hover:bg-gray-100' }}">
                            {{ $rotulo }}
                        </a>
                    @endforeach
                </div>

                {{-- Período: padrão hoje, lembrado na sessão (as abas acima não precisam repassá-lo).
                     Limpar manda ?periodo=hoje para não reabrir o período lembrado. --}}
                <x-filtro-periodo :action="route('sales.index')" :limpar="route('sales.index', array_filter(['situacao' => $situacao, 'periodo' => 'hoje']))"
                                  :periodo="$periodo" :de="$de" :ate="$ate" class="mb-6">
                    @if ($situacao)
                        <input type="hidden" name="situacao" value="{{ $situacao }}">
                    @endif
                </x-filtro-periodo>

                    <table class="tabela-cartoes min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Data</th>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Cliente</th>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-base font-semibold text-gray-700">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($sales as $sale)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td data-rotulo="Data" class="px-6 py-4 whitespace-nowrap text-lg text-gray-900">
                                        {{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td data-rotulo="Cliente" class="px-6 py-4 whitespace-nowrap text-lg text-gray-900 font-medium">
                                        {{ $sale->customer?->name ?? 'Cliente removido' }}</td>
                                    <td data-rotulo="Total" class="px-6 py-4 whitespace-nowrap text-lg font-bold text-gray-900">R$
                                        {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                                    <td data-rotulo="Status" class="px-6 py-4 whitespace-nowrap">
                                        {{-- Era um <select onchange="this.form.submit()">: uma seta do teclado ou
                                             a roda do mouse marcava a venda como Paga e gravava, sem intenção e
                                             sem volta pela interface. Agora é um botão, que só se aperta de
                                             propósito (§A6). --}}
                                        @if ($sale->status === 'paid')
                                            <x-status-badge cor="verde">Pago</x-status-badge>
                                        @elseif ($sale->status === 'cancelled')
                                            <x-status-badge cor="vermelho">Cancelada</x-status-badge>
                                        @else
                                            <p class="mb-2 text-base font-bold text-yellow-800">Pendente</p>

                                            <form action="{{ route('sales.updateStatus', $sale) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="paid">

                                                <button type="submit" data-rotulo-enviando="Registrando pagamento..."
                                                    class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border-2 border-green-700 px-5 py-3 text-base font-bold text-green-800 transition-colors hover:bg-green-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-700"
                                                    onclick="return confirm('Marcar como paga a compra de ' + @js($sale->customer?->name ?? 'cliente removido') + ' no valor de R$ {{ number_format($sale->total_amount, 2, ',', '.') }}?')">
                                                    Marcar como Pago
                                                </button>
                                            </form>

                                        @endif
                                    </td>
                                    <td data-rotulo="Ações" class="px-6 py-4">
                                        <div class="flex justify-end">
                                            <a href="{{ route('sales.show', $sale) }}"
                                                class="inline-flex min-h-11 items-center rounded-lg border-2 border-brand-700 px-5 py-3 text-base font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                                                Ver Detalhes
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
                                        @elseif ($situacao || $periodo !== 'hoje')
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
                <div class="mt-6">
                    {{ $sales->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
