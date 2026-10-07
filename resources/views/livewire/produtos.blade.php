<div>
    <x-busca-viva rotulo="Buscar produto pelo nome" exemplo="Ex.: Coca Cola" />

    {{-- Filtro pelo selo da coluna Status. Continuam links de verdade (nova aba,
         sem JS); o clique normal só troca a lista, como as abas de Vendas. --}}
    <p id="filtro-estoque" class="mb-1 text-lg font-bold text-ink">Filtrar pelo estoque</p>
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap" role="group" aria-labelledby="filtro-estoque">
        @foreach (['' => 'Todos', 'baixo' => 'Baixo Estoque', 'normal' => 'Normal'] as $valor => $rotulo)
            @php $ativo = $status === $valor; @endphp
            <a href="{{ route('products.index', array_filter(['status' => $valor, 'busca' => $termo])) }}"
               wire:click.prevent="$set('status', '{{ $valor }}')"
               @if ($ativo) aria-current="true" @endif
               class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 px-2 py-1 text-center text-base leading-tight font-bold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 sm:px-4 {{ $ativo ? 'border-brand-700 bg-brand-700 text-white' : 'border-edge text-ink hover:bg-gray-100' }}">
                {{ $rotulo }}
            </a>
        @endforeach
    </div>

    {{-- A contagem diz por extenso que a busca rodou (quem não viu a tabela piscar,
         ou usa leitor de tela, também fica sabendo). --}}
    <p class="mb-4 text-base font-semibold text-ink" aria-live="polite">
        <span wire:loading.remove>{{ $products->total() }} {{ $products->total() === 1 ? 'produto encontrado' : 'produtos encontrados' }}</span>
        <span wire:loading>Buscando...</span>
    </p>

    {{-- wire:key muda com filtro e página: o bloco é recriado e a seleção do lote
         (Alpine) começa do zero, com "todos" sendo os ids da lista nova. --}}
    <div wire:key="lista-{{ md5($termo) }}-{{ $status }}-{{ $products->currentPage() }}"
         x-data="{ sel: [], todos: @js($products->pluck('id')->map(fn ($id) => (string) $id)) }">
        @if ($products->isNotEmpty())
            <x-lote :rota="route('products.arquivarSelecionados')" metodo="DELETE" rotulo="Arquivar" enviando="Arquivando..."
                aviso="Eles saem da sua lista. As vendas já registradas continuam completas, e dá para restaurar em Arquivados." />
        @endif

        <table wire:loading.class="opacity-50" class="tabela-cartoes min-w-full divide-y divide-gray-200 transition-opacity">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-0 px-4 py-3"><span class="sr-only">Selecionar</span></th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Nome</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Preço Venda</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Lucro por unidade</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Estoque</th>
                    <th class="hidden px-4 py-3 text-left text-base font-semibold text-gray-700 lg:table-cell">Status</th>
                    <th class="px-4 py-3 text-right text-base font-semibold text-gray-700">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr wire:key="produto-{{ $product->id }}" class="hover:bg-gray-50 transition-colors" :class="sel.includes('{{ $product->id }}') && 'bg-brand-100/60'">
                        <x-lote-marcar :id="$product->id" :nome="$product->name" />
                        <td data-rotulo="Nome" class="px-4 py-4 text-lg font-medium text-gray-900">
                            {{ $product->name }}</td>
                        <td data-rotulo="Preço Venda" class="px-4 py-4 whitespace-nowrap text-lg text-gray-600">R$
                            {{ number_format($product->sale_price, 2, ',', '.') }}</td>
                        {{-- Sem custo não há lucro conhecido: dizer isso, não mostrar o preço inteiro como lucro. --}}
                        <td data-rotulo="Lucro por unidade" class="px-4 py-4 text-lg">
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
                            class="px-4 py-4 whitespace-nowrap text-lg font-bold {{ $product->stock_quantity <= $product->stock_alert ? 'text-red-600' : 'text-ink' }}">
                            {{ $product->stock_quantity }}
                        </td>
                        {{-- Some só entre md e lg, onde a tabela não cabe: o número do estoque
                             já fica vermelho. No cartão do celular o CSS da tabela-cartoes a mostra. --}}
                        <td data-rotulo="Status" class="hidden px-4 py-4 whitespace-nowrap lg:table-cell">
                            @if ($product->stock_quantity <= $product->stock_alert)
                                <x-status-badge cor="vermelho">Baixo Estoque</x-status-badge>
                            @else
                                <x-status-badge cor="verde">Normal</x-status-badge>
                            @endif
                        </td>
                        <td data-rotulo="Ações" class="px-4 py-4">
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
                        <td colspan="7">
                            @if ($termo !== '' || $status !== '')
                                <x-empty-state :acao="route('products.index')" rotulo="Ver todos os produtos">
                                    @if ($termo !== '')
                                        Nenhum produto com "{{ $termo }}" no nome{{ ['baixo' => ' está com baixo estoque', 'normal' => ' está com estoque normal', '' => ''][$status] }}.
                                    @else
                                        {{ $status === 'baixo' ? 'Nenhum produto com baixo estoque. Tudo abastecido.' : 'Nenhum produto com estoque normal.' }}
                                    @endif
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
    </div>

    <div class="mt-6">
        {{ $products->links('vendor.pagination.tailwind', ['livewire' => true]) }}
    </div>
</div>
