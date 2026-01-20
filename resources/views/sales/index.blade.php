<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Registro de Compras') }}
            </h2>
            <a href="{{ route('sales.create') }}"
                class="bg-[#0047AB] hover:bg-[#0056D2] text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Nova Venda
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Sucesso!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cliente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sales as $sale)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-900">
                                        {{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-900 font-medium">
                                        {{ $sale->customer->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-gray-900">R$
                                        {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('sales.updateStatus', $sale) }}" method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" onchange="this.form.submit()"
                                                class="rounded-full text-sm font-semibold px-3 py-1 w-36 {{ $sale->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}"
                                                {{ $sale->status === 'paid' ? 'disabled' : '' }}
                                                >
                                                <option value="pending" @selected($sale->status === 'pending')>
                                                    Pendente
                                                </option>
                                                <option value="paid" @selected($sale->status === 'paid')>
                                                    Pago
                                                </option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('sales.show', $sale) }}"
                                            class="text-indigo-600 hover:text-indigo-900 text-lg">Ver Detalhes</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
