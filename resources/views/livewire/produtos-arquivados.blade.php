<div>
    <x-busca-viva rotulo="Buscar produto arquivado pelo nome" exemplo="Ex.: Coca Cola" />

    <p class="mb-4 text-base font-semibold text-ink" aria-live="polite">
        <span wire:loading.remove>{{ $products->total() }} {{ $products->total() === 1 ? 'produto arquivado' : 'produtos arquivados' }}</span>
        <span wire:loading>Buscando...</span>
    </p>

    {{-- wire:key muda com busca e página: a seleção do lote recomeça com a lista nova. --}}
    <div wire:key="lista-{{ md5($termo) }}-{{ $products->currentPage() }}"
         x-data="{ sel: [], todos: @js($products->pluck('id')->map(fn ($id) => (string) $id)) }">
        @if ($products->isNotEmpty())
            <x-lote :rota="route('products.restaurarSelecionados')" metodo="PATCH" rotulo="Restaurar" enviando="Restaurando..." />
        @endif

        <table wire:loading.class="opacity-50" class="tabela-cartoes min-w-full divide-y divide-gray-200 transition-opacity">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-0 px-4 py-3"><span class="sr-only">Selecionar</span></th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Nome</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Preço Venda</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Estoque</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Arquivado em</th>
                    <th class="px-4 py-3 text-right text-base font-semibold text-gray-700">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr wire:key="arquivado-{{ $product->id }}" class="hover:bg-gray-50 transition-colors" :class="sel.includes('{{ $product->id }}') && 'bg-brand-100/60'">
                        <x-lote-marcar :id="$product->id" :nome="$product->name" />
                        <td data-rotulo="Nome" class="px-4 py-4 text-lg font-medium text-gray-900">
                            {{ $product->name }}</td>
                        <td data-rotulo="Preço Venda" class="px-4 py-4 whitespace-nowrap text-lg text-gray-600">R$
                            {{ number_format($product->sale_price, 2, ',', '.') }}</td>
                        <td data-rotulo="Estoque" class="px-4 py-4 whitespace-nowrap text-lg font-bold text-ink">
                            {{ $product->stock_quantity }}</td>
                        <td data-rotulo="Arquivado em" class="px-4 py-4 whitespace-nowrap text-lg text-gray-600">
                            {{ $product->deleted_at->format('d/m/Y') }}</td>
                        <td data-rotulo="Ações" class="px-4 py-4">
                            <div class="flex justify-end">
                                <form action="{{ route('products.restaurar', $product) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" data-rotulo-enviando="Restaurando..."
                                        class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-lg border-2 border-brand-700 px-5 py-3 text-base font-bold text-brand-700 transition-colors hover:bg-brand-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                                        Restaurar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            @if ($termo !== '')
                                <x-empty-state :acao="route('products.arquivados')" rotulo="Ver todos os arquivados">
                                    Nenhum produto arquivado com "{{ $termo }}" no nome.
                                </x-empty-state>
                            @else
                                <x-empty-state :acao="route('products.index')" rotulo="Voltar para os produtos">
                                    Nenhum produto arquivado.
                                </x-empty-state>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $products->links('vendor.pagination.tailwind', ['livewire' => true]) }}
    </div>
</div>
