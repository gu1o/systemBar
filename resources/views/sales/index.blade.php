<x-app-layout>
    <x-slot name="titulo">Registro de Compras</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Registro de Compras')">
            <a href="{{ route('sales.create') }}"
                class="bg-brand-700 hover:bg-brand-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Nova Venda
            </a>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Sem padding: as faixas de filtro e a tabela vão de borda a borda. --}}
            <x-card padding="p-0">
                {{-- Filtros, busca e tabela respondem sem recarregar: app/Livewire/Vendas.php. --}}
                <livewire:vendas />
            </x-card>
        </div>
    </div>
</x-app-layout>
