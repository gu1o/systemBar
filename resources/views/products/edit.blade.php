<x-app-layout>
    <x-slot name="titulo">Editar Produto</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Editar Produto')" />
    </x-slot>

    <x-currency-mask />

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card padding="p-8">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <x-form-errors />

                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 text-xl font-bold mb-2">
                            Nome do Produto
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $product->name) }}"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400 @error('name') border-2 border-red-600 @enderror"
                            @error('name') aria-invalid="true" @enderror
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
                                name="cost_price"
                                id="cost_price"
                                value="{{ old('cost_price', $product->cost_price === null ? '' : number_format($product->cost_price, 2, ',', '.')) }}"
                                class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                                placeholder="0,00" inputmode="decimal" data-mask="0000000.000" oninput="brlCurrencyMask(event)"
                            >
                        <p class="mt-1 text-base text-gray-600">Digite só os números: 1234 vira 12,34.</p>
                        </div>

                        <div>
                            <label for="sale_price" class="block text-gray-700 text-xl font-bold mb-2">
                                Preço de Venda (R$)
                            </label>
                            <input
                                type="text"
                                name="sale_price"
                                id="sale_price"
                                value="{{ old('sale_price', number_format($product->sale_price, 2, ',', '.')) }}"
                                class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400 @error('sale_price') border-2 border-red-600 @enderror"
                                @error('sale_price') aria-invalid="true" @enderror
                                placeholder="0,00" inputmode="decimal" data-mask="0000000.000" required oninput="brlCurrencyMask(event)"
                            >
                        <p class="mt-1 text-base text-gray-600">Digite só os números: 1234 vira 12,34.</p>
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
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400 @error('stock_quantity') border-2 border-red-600 @enderror"
                            @error('stock_quantity') aria-invalid="true" @enderror
                            placeholder="10"
                            min="0"
                            required
                        >
                    </div>

                    <div class="mb-8">
                        <label for="stock_alert" class="block text-gray-700 text-xl font-bold mb-2">
                            Avisar quando o estoque chegar em
                        </label>
                        <input
                            type="number"
                            inputmode="numeric"
                            name="stock_alert"
                            id="stock_alert"
                            value="{{ old('stock_alert', $product->stock_alert) }}"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400 @error('stock_alert') border-2 border-red-600 @enderror"
                            @error('stock_alert') aria-invalid="true" @enderror
                            aria-describedby="stock_alert-ajuda"
                            min="0"
                            placeholder="5"
                        >
                        <p id="stock_alert-ajuda" class="mt-1 text-base text-gray-600">
                            O produto aparece como "Baixo Estoque" a partir desta quantidade. Cerveja e detergente não se repõem no mesmo ponto.
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('products.index') }}"
                           class="text-gray-600 hover:text-red-500 font-bold text-lg transition-colors duration-300">
                            Cancelar
                        </a>

                        <x-button-submit>
                            Atualizar Produto
                        </x-button-submit>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
