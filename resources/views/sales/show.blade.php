<x-app-layout>
    <x-slot name="titulo">Detalhes da Venda</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Detalhes da Venda').' #'.$sale->id">
            <a href="{{ route('sales.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                Voltar
            </a>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card padding="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 border-b pb-8">
                    <div>
                        <h3 class="text-gray-700 text-base font-bold">Cliente</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ $sale->customer?->name ?? 'Cliente removido' }}</p>
                        <p class="text-gray-600">{{ $sale->customer?->phone }}</p>
                    </div>
                    <div class="md:text-right">
                        <h3 class="text-gray-700 text-base font-bold">Data da Venda</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                        <x-status-badge :cor="['paid' => 'verde', 'pending' => 'amarelo', 'cancelled' => 'vermelho'][$sale->status]">
                            {{ ['paid' => 'Pago', 'pending' => 'Pendente', 'cancelled' => 'Cancelada'][$sale->status] }}
                        </x-status-badge>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-gray-700 text-xl font-bold mb-4">Itens da Compra</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Produto</th>
                                <th class="px-6 py-3 text-center text-base font-semibold text-gray-700">Qtd</th>
                                <th class="px-6 py-3 text-right text-base font-semibold text-gray-700">Preço Unit.</th>
                                <th class="px-6 py-3 text-right text-base font-semibold text-gray-700">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-900">{{ $item->product?->name ?? 'Produto removido' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-lg text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-lg text-gray-600">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-lg font-bold text-gray-900">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-xl font-bold text-gray-900">Total Geral:</td>
                                <td class="px-6 py-4 text-right text-2xl font-extrabold text-brand-700">R$ {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($sale->status !== 'cancelled')
                    <div class="mb-8 flex justify-center nao-imprimir">
                        <button type="button" x-data=""
                                x-on:click="$dispatch('open-modal', 'cancelar-venda')"
                                class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border-2 border-red-700 px-6 py-3 text-lg font-bold text-red-700 transition-colors hover:bg-red-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700">
                            Cancelar esta Compra
                        </button>
                    </div>

                    <x-modal name="cancelar-venda" maxWidth="lg" focusable>
                        <div class="p-6 text-left">
                            <h2 class="text-2xl font-bold text-gray-900">
                                Cancelar a compra de {{ $sale->customer?->name ?? 'cliente removido' }}, de R$ {{ number_format($sale->total_amount, 2, ',', '.') }}?
                            </h2>

                            <p class="mt-3 text-lg text-gray-700">
                                Os produtos voltam para o estoque e a compra continua na lista, marcada como cancelada.
                            </p>

                            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <button type="button" x-on:click="$dispatch('close')"
                                        class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border-2 border-gray-400 px-6 py-3 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-100">
                                    Manter a compra
                                </button>

                                <form action="{{ route('sales.cancel', $sale) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-lg bg-red-700 px-6 py-3 text-lg font-bold text-white transition-colors hover:bg-red-800">
                                        Cancelar a compra
                                    </button>
                                </form>
                            </div>
                        </div>
                    </x-modal>
                @endif

                <div class="flex justify-center nao-imprimir">
                    <button onclick="window.print()" class="bg-brand-700 hover:bg-brand-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all text-xl flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir Comprovante
                    </button>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
