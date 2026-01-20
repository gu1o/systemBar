<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Registrar Nova Venda') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('sales.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-8">
                        <label for="customer_id" class="block text-gray-700 text-xl font-bold mb-2">Selecionar Cliente</label>
                        <select name="customer_id" id="customer_id" class="shadow border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg" required>
                            <option value="">Escolha um cliente...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-gray-700 text-xl font-bold mb-4">Produtos da Venda</h3>
                        <div id="items-container">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border rounded bg-gray-50">
                                <div class="md:col-span-2">
                                    <label class="block text-gray-600 text-sm font-bold mb-1">Produto</label>
                                    <select name="items[0][product_id]" class="w-full border rounded py-2 px-3 text-lg" required>
                                        <option value="">Selecione um produto...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} - R$ {{ number_format($product->sale_price, 2, ',', '.') }} (Estoque: {{ $product->stock_quantity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-sm font-bold mb-1">Quantidade</label>
                                    <input type="number" name="items[0][quantity]" min="1" value="1" class="w-full border rounded py-2 px-3 text-lg" required>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="mt-2 text-blue-600 font-bold hover:text-blue-800 text-lg">+ Adicionar outro produto</button>
                    </div>

                    <div class="flex items-center justify-between border-t pt-8">
                        <button type="submit" class="bg-[#0047AB] hover:bg-[#0056D2] text-white font-bold py-4 px-10 rounded-lg shadow-lg transition-all text-2xl">
                            Finalizar Venda
                        </button>
                        <a href="{{ route('sales.index') }}" class="text-gray-600 hover:text-gray-900 font-bold text-lg">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 1;
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border rounded bg-gray-50';
            newItem.innerHTML = `
                <div class="md:col-span-2">
                    <label class="block text-gray-600 text-sm font-bold mb-1">Produto</label>
                    <select name="items[${itemIndex}][product_id]" class="w-full border rounded py-2 px-3 text-lg" required>
                        <option value="">Selecione um produto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} - R$ {{ number_format($product->sale_price, 2, ',', '.') }} (Estoque: {{ $product->stock_quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-bold mb-1">Quantidade</label>
                    <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1" class="w-full border rounded py-2 px-3 text-lg" required>
                </div>
            `;
            container.appendChild(newItem);
            itemIndex++;
        });
    </script>
</x-app-layout>
