<div>
    <x-busca-viva rotulo="Buscar cliente pelo nome" exemplo="Ex.: Maria" />

    <p class="mb-4 text-base font-semibold text-ink" aria-live="polite">
        <span wire:loading.remove>{{ $customers->total() }} {{ $customers->total() === 1 ? 'cliente encontrado' : 'clientes encontrados' }}</span>
        <span wire:loading>Buscando...</span>
    </p>

    {{-- wire:key muda com busca e página: a seleção do lote recomeça com a lista nova. --}}
    <div wire:key="lista-{{ md5($termo) }}-{{ $customers->currentPage() }}"
         x-data="{ sel: [], todos: @js($customers->pluck('id')->map(fn ($id) => (string) $id)) }">
        @if ($customers->isNotEmpty())
            <x-lote :rota="route('customers.arquivarSelecionados')" metodo="DELETE" rotulo="Arquivar" enviando="Arquivando..."
                aviso="Eles saem da sua lista. As compras deles continuam no histórico, e dá para restaurar em Arquivados." />
        @endif

        <table wire:loading.class="opacity-50" class="tabela-cartoes min-w-full divide-y divide-gray-200 transition-opacity">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-0 px-4 py-3"><span class="sr-only">Selecionar</span></th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Nome</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Telefone</th>
                    <th class="px-4 py-3 text-right text-base font-semibold text-gray-700">Ações</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($customers as $customer)
                    <tr wire:key="cliente-{{ $customer->id }}" class="hover:bg-gray-50 transition-colors" :class="sel.includes('{{ $customer->id }}') && 'bg-brand-100/60'">
                        <x-lote-marcar :id="$customer->id" :nome="$customer->name" />
                        <td data-rotulo="Nome" class="px-4 py-4 text-lg font-medium text-gray-900">
                            {{ $customer->name }}
                        </td>
                        <td data-rotulo="Telefone" class="px-4 py-4 text-lg text-gray-600">
                            {{ $customer->phone ?? '-' }}
                        </td>
                        <td data-rotulo="Ações" class="px-4 py-4">
                            <x-row-actions
                                tipo="cliente"
                                :nome="$customer->name"
                                :editar="route('customers.edit', $customer)"
                                :excluir="route('customers.destroy', $customer)"
                                :aviso="($customer->sales_count > 0
                                    ? 'Este cliente tem '.$customer->sales_count.' '.($customer->sales_count === 1 ? 'compra registrada' : 'compras registradas').'. '
                                    : '').'Ele sai da sua lista. As compras dele continuam no histórico.'" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            @if ($termo !== '')
                                <x-empty-state :acao="route('customers.index')" rotulo="Ver todos os clientes">
                                    Nenhum cliente com "{{ $termo }}" no nome.
                                </x-empty-state>
                            @else
                                <x-empty-state :acao="route('customers.create')" rotulo="Cadastrar meu primeiro cliente">
                                    Você ainda não cadastrou nenhum cliente. Toda venda é registrada no nome de um.
                                </x-empty-state>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $customers->links('vendor.pagination.tailwind', ['livewire' => true]) }}
    </div>
</div>
