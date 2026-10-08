<x-app-layout>
    <x-slot name="titulo">Clientes</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Clientes')">
            <div class="flex gap-3">
                <a href="{{ route('customers.arquivados') }}"
                    class="flex items-center rounded-lg border-2 border-white/70 px-4 py-1.5 font-bold text-white transition-colors hover:bg-white/10">
                    Arquivados
                </a>
                <a href="{{ route('customers.create') }}"
                    class="hidden md:flex bg-accent-700 hover:bg-accent-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                    + Novo Cliente
                </a>
            </div>
        </x-page-header>
    </x-slot>

    {{-- pb-28 no celular: o botão flutuante de cadastrar não cobre o último cartão no fim da rolagem. --}}
    <div class="pt-12 pb-28 md:pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card>
                {{-- Busca e tabela respondem sem recarregar: app/Livewire/Clientes.php. --}}
                <livewire:clientes />
            </x-card>
            <a href="{{ route('customers.create') }}" title="{{ __('Cadastrar novo cliente') }}"
                class="flex fixed bottom-6 right-4 bg-accent-700 hover:bg-accent-600 text-white font-bold p-4 rounded-full shadow-lg transition-all z-10 md:hidden">
                <span class="sr-only">{{ __('Cadastrar novo cliente') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" aria-hidden="true">
                    <path d="M12 5V19M5 12H19" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
        </div>
    </div>
</x-app-layout>
