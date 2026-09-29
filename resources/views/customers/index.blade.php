<x-app-layout>
    <x-slot name="titulo">Clientes</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Clientes')">
            <a href="{{ route('customers.create') }}"
                class="bg-accent-700 hover:bg-accent-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Novo Cliente
            </a>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card>
                <x-search-form :rota="route('customers.index')" :valor="$busca" rotulo="Buscar cliente pelo nome" exemplo="Ex.: Maria" />

                <table class="tabela-cartoes min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Nome</th>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Telefone</th>
                            <th class="px-6 py-3 text-right text-base font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td data-rotulo="Nome" class="px-6 py-4 text-lg font-medium text-gray-900">
                                    {{ $customer->name }}
                                </td>
                                <td data-rotulo="Telefone" class="px-6 py-4 text-lg text-gray-600">
                                    {{ $customer->phone ?? '-' }}
                                </td>
                                <td data-rotulo="Ações" class="px-6 py-4">
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
                                <td colspan="3">
                                    @if ($busca !== '')
                                        <x-empty-state :acao="route('customers.index')" rotulo="Ver todos os clientes">
                                            Nenhum cliente com "{{ $busca }}" no nome.
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

                <div class="mt-6">
                    {{ $customers->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
