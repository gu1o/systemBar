<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Produto') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 text-xl font-bold mb-2">
                            Nome do Produto
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $product->name) }}"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                            required
                            placeholder="Coca Cola 2L"
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="cost_price" class="block text-gray-700 text-xl font-bold mb-2">
                                Preço de Custo (R$) <span class="text-base font-normal text-gray-500">(opcional)</span>
                            </label>
                            <input
                                type="text"
                                step="0.01"
                                name="cost_price"
                                id="cost_price"
                                value="{{ old('cost_price', $product->cost_price) }}"
                                class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                                placeholder="0,00" data-mask="0000000.000" oninput="brlCurrencyMask(event)"
                            >
                        </div>

                        <div>
                            <label for="sale_price" class="block text-gray-700 text-xl font-bold mb-2">
                                Preço de Venda (R$)
                            </label>
                            <input
                                type="text"
                                step="0.01"
                                name="sale_price"
                                id="sale_price"
                                value="{{ old('sale_price', $product->sale_price) }}"
                                class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                                placeholder="0,00" data-mask="0000000.000" required oninput="brlCurrencyMask(event)"
                            >
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="stock_quantity" class="block text-gray-700 text-xl font-bold mb-2">
                            Quantidade em Estoque
                        </label>
                        <input
                            type="number"
                            name="stock_quantity"
                            id="stock_quantity"
                            value="{{ old('stock_quantity', $product->stock_quantity) }}"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                            placeholder="10"
                            required
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('products.index') }}"
                           class="text-gray-600 hover:text-red-500 font-bold text-lg transtition-colors duration-300">
                            Cancelar
                        </a>

                        <x-button-submit>
                            Atualizar Produto
                        </x-button-submit>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
