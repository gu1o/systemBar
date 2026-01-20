<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Cadastrar Novo Produto') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 text-xl font-bold mb-2">Nome do Produto</label>
                        <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="cost_price" class="block text-gray-700 text-xl font-bold mb-2">Preço de Custo (R$)</label>
                            <input type="number" step="0.01" name="cost_price" id="cost_price" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg" required>
                        </div>
                        <div>
                            <label for="sale_price" class="block text-gray-700 text-xl font-bold mb-2">Preço de Venda (R$)</label>
                            <input type="number" step="0.01" name="sale_price" id="sale_price" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg" required>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="stock_quantity" class="block text-gray-700 text-xl font-bold mb-2">Quantidade em Estoque</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg" required>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-[#008080] hover:bg-[#00A0A0] text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all text-xl">
                            Salvar Produto
                        </button>
                        <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900 font-bold text-lg">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
