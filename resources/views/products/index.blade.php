<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Estoque de Produtos') }}
            </h2>
            <a href="{{ route('products.create') }}"
                class="hidden md:flex bg-[#008080] hover:bg-[#00A0A0] text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Novo Produto
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative">
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
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Nome</th>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Preço Venda</th>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Estoque</th>
                                <th
                                    class="px-6 py-3 text-left text-base font-semibold text-gray-700">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-base font-semibold text-gray-700">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-lg font-medium text-gray-900">
                                        {{ $product->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-600">R$
                                        {{ number_format($product->sale_price, 2, ',', '.') }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-lg font-bold {{ $product->stock_quantity <= 5 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $product->stock_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($product->stock_quantity <= 5)
                                            <span
                                                class="px-4 py-2 inline-flex text-base font-semibold rounded-fullbg-red-100 text-red-800">Baixo
                                                Estoque</span>
                                        @else
                                            <span
                                                class="px-4 py-2 inline-flex text-base font-semibold rounded-fullbg-green-100 text-green-800">Normal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-end gap-3">
                                            <a href="{{ route('products.edit', $product) }}"
                                                class="inline-flex min-h-11 items-center rounded-lg border-2 border-[#0047AB] px-5 py-3 text-base font-bold text-[#0047AB] transition-colors hover:bg-[#0047AB] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0047AB]">
                                                Editar
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border-2 border-red-700 px-5 py-3 text-base font-bold text-red-700 transition-colors hover:bg-red-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700"
                                                    onclick="return confirm('Excluir o produto ' + @js($product->name) + '? Esta ação não pode ser desfeita.')">
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </div>
            <a href="{{ route('products.create') }}" title="{{ __('Cadastrar novo produto') }}"
                class="flex absolute bottom-0 right-4 bg-[#008080] hover:bg-[#00A0A0] text-white font-bold p-3 rounded-full shadow-md transition-all z-10 md:hidden">
                <span class="sr-only">{{ __('Cadastrar novo produto') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" aria-hidden="true">
                    <path d="M12 5V19M5 12H19" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
        </div>
    </div>
</x-app-layout>
