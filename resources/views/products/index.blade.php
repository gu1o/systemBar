<x-app-layout>
    <x-slot name="titulo">Estoque de Produtos</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Estoque de Produtos')">
            <a href="{{ route('products.create') }}"
                class="hidden md:flex bg-accent-700 hover:bg-accent-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Novo Produto
            </a>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <x-card>
                <x-search-form :rota="route('products.index')" :valor="$busca" rotulo="Buscar produto pelo nome" exemplo="Ex.: Coca Cola" />

                    <table class="tabela-cartoes min-w-full divide-y divide-gray-200">
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
                                    Lucro por unidade</th>
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
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td data-rotulo="Nome" class="px-6 py-4 whitespace-nowrap text-lg font-medium text-gray-900">
                                        {{ $product->name }}</td>
                                    <td data-rotulo="Preço Venda" class="px-6 py-4 whitespace-nowrap text-lg text-gray-600">R$
                                        {{ number_format($product->sale_price, 2, ',', '.') }}</td>
                                    {{-- Sem custo não há lucro conhecido: dizer isso, não mostrar o preço inteiro como lucro. --}}
                                    <td data-rotulo="Lucro por unidade" class="px-6 py-4 whitespace-nowrap text-lg">
                                        @if ($product->cost_price === null)
                                            <span class="text-ink-muted">Sem preço de custo</span>
                                        @else
                                            @php $lucro = $product->sale_price - $product->cost_price; @endphp
                                            <span class="font-bold {{ $lucro < 0 ? 'text-red-700' : 'text-green-800' }}">R$ {{ number_format($lucro, 2, ',', '.') }}</span>
                                            @if ($product->sale_price > 0)
                                                <span class="text-ink-muted">({{ number_format($lucro / $product->sale_price * 100, 0, ',', '.') }}%)</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td data-rotulo="Estoque"
                                        class="px-6 py-4 whitespace-nowrap text-lg font-bold {{ $product->stock_quantity <= $product->stock_alert ? 'text-red-600' : 'text-ink' }}">
                                        {{ $product->stock_quantity }}
                                    </td>
                                    <td data-rotulo="Status" class="px-6 py-4 whitespace-nowrap">
                                        @if ($product->stock_quantity <= $product->stock_alert)
                                            <x-status-badge cor="vermelho">Baixo Estoque</x-status-badge>
                                        @else
                                            <x-status-badge cor="verde">Normal</x-status-badge>
                                        @endif
                                    </td>
                                    <td data-rotulo="Ações" class="px-6 py-4">
                                        <x-row-actions
                                            tipo="produto"
                                            :nome="$product->name"
                                            :editar="route('products.edit', $product)"
                                            :excluir="route('products.destroy', $product)"
                                            aviso="Ele sai da sua lista. As vendas já registradas continuam completas." />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        @if ($busca !== '')
                                            <x-empty-state :acao="route('products.index')" rotulo="Ver todos os produtos">
                                                Nenhum produto com "{{ $busca }}" no nome.
                                            </x-empty-state>
                                        @else
                                            <x-empty-state :acao="route('products.create')" rotulo="Cadastrar meu primeiro produto">
                                                Você ainda não cadastrou nenhum produto. Comece pelo que mais vende.
                                            </x-empty-state>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </x-card>
            <a href="{{ route('products.create') }}" title="{{ __('Cadastrar novo produto') }}"
                class="flex absolute bottom-0 right-4 bg-accent-700 hover:bg-accent-600 text-white font-bold p-3 rounded-full shadow-md transition-all z-10 md:hidden">
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
