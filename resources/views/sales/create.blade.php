<x-app-layout>
    <x-slot name="titulo">Registrar Nova Venda</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Registrar Nova Venda')" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card padding="p-8">
                @php
                    // Sem cliente ou sem produto em estoque os <select> apareciam vazios e a
                    // venda era impossível, sem nenhuma explicação na tela (§A8).
                    $faltando = collect([
                        $customers->isEmpty() ? 'um cliente cadastrado' : null,
                        $products->isEmpty() ? 'um produto com estoque' : null,
                    ])->filter();
                @endphp

                @if ($faltando->isNotEmpty())
                    <x-empty-state
                        :acao="$customers->isEmpty() ? route('customers.create') : route('products.create')"
                        :rotulo="$customers->isEmpty() ? 'Cadastrar um cliente' : 'Cadastrar um produto'">
                        Para registrar uma venda falta {{ $faltando->join(' e ') }}.
                    </x-empty-state>
                @else
                <form action="{{ route('sales.store') }}" method="POST">
                    @csrf

                    <x-form-errors />

                    <div class="mb-8">
                        <label for="customer_id" class="block text-gray-700 text-xl font-bold mb-2">Selecionar Cliente</label>
                        <select name="customer_id" id="customer_id" class="shadow border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg @error('customer_id') border-2 border-red-600 @enderror" @error('customer_id') aria-invalid="true" @enderror required>
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
                                    <label for="item-0-produto" class="block text-gray-700 text-base font-bold mb-1">Produto</label>
                                    <select name="items[0][product_id]" id="item-0-produto" class="w-full border rounded py-3 px-3 text-lg" required>
                                        <option value="">Selecione um produto...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} - R$ {{ number_format($product->sale_price, 2, ',', '.') }} (Estoque: {{ $product->stock_quantity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="item-0-quantidade" class="block text-gray-700 text-base font-bold mb-1">Quantidade</label>
                                    <input type="number" inputmode="numeric" name="items[0][quantity]" id="item-0-quantidade" min="1" value="1" class="w-full border rounded py-3 px-3 text-lg" required>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="mt-2 text-blue-600 font-bold hover:text-blue-800 text-lg">+ Adicionar outro produto</button>
                    </div>

                    <div class="flex items-center justify-between border-t pt-8">
                        <button type="submit" class="bg-brand-700 hover:bg-brand-600 text-white font-bold py-4 px-10 rounded-lg shadow-lg transition-all text-2xl">
                            Finalizar Venda
                        </button>
                        <a href="{{ route('sales.index') }}" class="text-gray-600 hover:text-gray-900 font-bold text-lg">
                            Cancelar
                        </a>
                    </div>
                </form>
                @endif
            </x-card>
        </div>
    </div>

    <script>
        let itemIndex = 1;
        // ?. — sem cliente ou produto o formulário não é renderizado (§A8), e o botão não existe.
        document.getElementById('add-item')?.addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border rounded bg-gray-50';
            newItem.innerHTML = `
                <div class="md:col-span-2">
                    <label for="item-${itemIndex}-produto" class="block text-gray-700 text-base font-bold mb-1">Produto</label>
                    <select name="items[${itemIndex}][product_id]" id="item-${itemIndex}-produto" class="w-full border rounded py-3 px-3 text-lg" required>
                        <option value="">Selecione um produto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} - R$ {{ number_format($product->sale_price, 2, ',', '.') }} (Estoque: {{ $product->stock_quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="item-${itemIndex}-quantidade" class="block text-gray-700 text-base font-bold mb-1">Quantidade</label>
                    <input type="number" inputmode="numeric" name="items[${itemIndex}][quantity]" id="item-${itemIndex}-quantidade" min="1" value="1" class="w-full border rounded py-3 px-3 text-lg" required>
                </div>
            `;
            container.appendChild(newItem);
            itemIndex++;
        });
    </script>
</x-app-layout>
