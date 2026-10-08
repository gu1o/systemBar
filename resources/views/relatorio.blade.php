<x-app-layout>
    <x-slot name="titulo">Faturamento</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Faturamento')" />
    </x-slot>

    <div class="py-6 sm:py-12 print:py-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Só na impressão, como no comprovante de Vendas: nome do sistema e data juntos, no centro. --}}
            <div class="mb-4 hidden text-center print:block">
                <p class="text-xl font-bold">{{ config('app.name', 'System Bar') }}</p>
                <p class="text-sm">Relatório de faturamento emitido em {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            {{-- Filtro, totais e dia a dia respondem sem recarregar: app/Livewire/Faturamento.php. --}}
            <livewire:faturamento />
        </div>
    </div>
</x-app-layout>
