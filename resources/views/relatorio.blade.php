@php $reais = fn ($valor) => 'R$ '.number_format($valor, 2, ',', '.'); @endphp

<x-app-layout>
    <x-slot name="titulo">Faturamento</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Faturamento')" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-card>
                <x-filtro-periodo :action="route('relatorio')" :limpar="route('relatorio')"
                                  :periodo="$periodo" :de="$de" :ate="$ate" />
            </x-card>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <x-card padding="p-6">
                    <p class="text-lg font-bold text-ink-muted">Vendido</p>
                    <p class="mt-2 text-4xl font-extrabold text-brand-900">{{ $reais($vendido) }}</p>
                    <p class="mt-1 text-base text-ink-muted">{{ $compras }} {{ $compras === 1 ? 'compra' : 'compras' }}</p>
                </x-card>

                <x-card padding="p-6">
                    <p class="text-lg font-bold text-ink-muted">Lucro</p>
                    <p class="mt-2 text-4xl font-extrabold {{ $lucro < 0 ? 'text-red-700' : 'text-green-800' }}">{{ $reais($lucro) }}</p>
                    @if ($itensSemCusto > 0)
                        <p class="mt-1 text-base text-ink-muted">
                            {{ $itensSemCusto }} {{ $itensSemCusto === 1 ? 'produto vendido sem preço de custo ficou' : 'produtos vendidos sem preço de custo ficaram' }} fora da conta.
                        </p>
                    @endif
                </x-card>

                <x-card padding="p-6">
                    <p class="text-lg font-bold text-ink-muted">Ainda a receber</p>
                    <p class="mt-2 text-4xl font-extrabold text-brand-900">{{ $reais($aReceber) }}</p>
                    <p class="mt-1 text-base text-ink-muted">das compras deste período</p>
                </x-card>

                <x-card padding="p-6">
                    <p class="text-lg font-bold text-ink-muted">Média por compra</p>
                    <p class="mt-2 text-4xl font-extrabold text-brand-900">{{ $reais($compras ? $vendido / $compras : 0) }}</p>
                </x-card>
            </div>

            <x-card>
                <h3 class="mb-4 text-2xl font-bold text-ink">Dia a dia</h3>

                <table class="tabela-cartoes min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Dia</th>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Compras</th>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Vendido</th>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Lucro</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($dias as $dia => $totais)
                            <tr>
                                <td data-rotulo="Dia" class="px-6 py-4 text-lg font-medium text-gray-900">
                                    {{ \Illuminate\Support\Carbon::parse($dia)->translatedFormat('d/m/Y (D)') }}
                                </td>
                                <td data-rotulo="Compras" class="px-6 py-4 text-lg text-gray-900">{{ $totais['compras'] }}</td>
                                <td data-rotulo="Vendido" class="px-6 py-4 text-lg font-bold text-gray-900">{{ $reais($totais['vendido']) }}</td>
                                <td data-rotulo="Lucro" class="px-6 py-4 text-lg font-bold {{ $totais['lucro'] < 0 ? 'text-red-700' : 'text-green-800' }}">{{ $reais($totais['lucro']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state :acao="route('sales.create')" rotulo="Registrar uma venda">
                                        Nenhuma venda neste período.
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <button type="button" onclick="window.print()"
                        class="nao-imprimir mt-6 inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border-2 border-brand-700 px-6 py-3 text-lg font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white">
                    Imprimir relatório
                </button>
            </x-card>
        </div>
    </div>
</x-app-layout>
