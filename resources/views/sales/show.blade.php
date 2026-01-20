<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalhes da Venda') }} #{{ $sale->id }}
            </h2>
            <a href="{{ route('sales.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 border-b pb-8">
                    <div>
                        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Cliente</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ $sale->customer->name }}</p>
                        <p class="text-gray-600">{{ $sale->customer->phone }}</p>
                    </div>
                    <div class="md:text-right">
                        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Data da Venda</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $sale->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $sale->status == 'paid' ? 'Pago' : 'Pendente' }}
                        </span>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-gray-700 text-xl font-bold mb-4">Itens da Compra</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Preço Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-900">{{ $item->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-lg text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-lg text-gray-600">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-lg font-bold text-gray-900">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-xl font-bold text-gray-900">Total Geral:</td>
                                <td class="px-6 py-4 text-right text-2xl font-extrabold text-[#0047AB]">R$ {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-center">
                    <button onclick="window.print()" class="bg-[#0047AB] hover:bg-[#0056D2] text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all text-xl flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir Comprovante
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
