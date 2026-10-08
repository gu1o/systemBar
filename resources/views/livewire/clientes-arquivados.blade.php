<div>
    <x-busca-viva rotulo="Buscar cliente arquivado pelo nome" exemplo="Ex.: Maria" />

    <p class="mb-4 text-base font-semibold text-ink" aria-live="polite">
        <span wire:loading.remove>{{ $customers->total() }} {{ $customers->total() === 1 ? 'cliente arquivado' : 'clientes arquivados' }}</span>
        <span wire:loading>Buscando...</span>
    </p>

    {{-- wire:key muda com busca e página: a seleção do lote recomeça com a lista nova. --}}
    <div wire:key="lista-{{ md5($termo) }}-{{ $customers->currentPage() }}"
         x-data="{ sel: [], todos: @js($customers->pluck('id')->map(fn ($id) => (string) $id)) }">
        @if ($customers->isNotEmpty())
            <x-lote :rota="route('customers.restaurarSelecionados')" metodo="PATCH" rotulo="Restaurar" enviando="Restaurando..." />
        @endif

        <table wire:loading.class="opacity-50" class="tabela-cartoes min-w-full divide-y divide-gray-200 transition-opacity">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-0 px-4 py-3"><span class="sr-only">Selecionar</span></th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Nome</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Telefone</th>
                    <th class="px-4 py-3 text-left text-base font-semibold text-gray-700">Arquivado em</th>
                    <th class="px-4 py-3 text-right text-base font-semibold text-gray-700">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($customers as $customer)
                    <tr wire:key="arquivado-{{ $customer->id }}" class="hover:bg-gray-50 transition-colors" :class="sel.includes('{{ $customer->id }}') && 'bg-brand-100/60'">
                        <x-lote-marcar :id="$customer->id" :nome="$customer->name" />
                        <td data-rotulo="Nome" class="px-4 py-4 text-lg font-medium text-gray-900">
                            {{ $customer->name }}</td>
                        <td data-rotulo="Telefone" class="px-4 py-4 whitespace-nowrap text-lg text-gray-600">
                            {{ $customer->phone ?? '-' }}</td>
                        <td data-rotulo="Arquivado em" class="px-4 py-4 whitespace-nowrap text-lg text-gray-600">
                            {{ $customer->deleted_at->format('d/m/Y') }}</td>
                        <td data-rotulo="Ações" class="px-4 py-4">
                            <div class="flex justify-end">
                                <form action="{{ route('customers.restaurar', $customer) }}" method="POST">
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
                        <td colspan="5">
                            @if ($termo !== '')
                                <x-empty-state :acao="route('customers.arquivados')" rotulo="Ver todos os arquivados">
                                    Nenhum cliente arquivado com "{{ $termo }}" no nome.
                                </x-empty-state>
                            @else
                                <x-empty-state :acao="route('customers.index')" rotulo="Voltar para os clientes">
                                    Nenhum cliente arquivado.
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
