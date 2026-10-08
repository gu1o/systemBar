<x-app-layout>
    <x-slot name="titulo">Clientes Arquivados</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Clientes Arquivados')">
            <a href="{{ route('customers.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                Voltar
            </a>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card>
                <p class="mb-6 text-lg text-ink-muted">
                    Clientes arquivados não aparecem na lista nem no registro de vendas. As compras deles continuam no histórico. Restaurar traz o cliente de volta.
                </p>

                {{-- Busca, lote e tabela respondem sem recarregar: app/Livewire/ClientesArquivados.php. --}}
                <livewire:clientes-arquivados />
            </x-card>
        </div>
    </div>
</x-app-layout>
