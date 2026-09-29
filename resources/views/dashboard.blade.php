<x-app-layout>
    <x-slot name="titulo">Painel de Controle</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Painel de Controle')" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- §F1 — os quatro números que o dono do comércio hoje descobre somando
                 na calculadora. "A receber" é o mais útil: é dinheiro na rua. --}}
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <x-card padding="p-6">
                    <p class="text-lg font-bold text-ink-muted">Vendido hoje</p>
                    <p class="mt-2 text-4xl font-extrabold text-brand-900">R$ {{ number_format($vendidoHoje, 2, ',', '.') }}</p>
                </x-card>

                <a href="{{ route('relatorio') }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                <x-card padding="p-6" class="h-full transition-colors hover:border-brand-700">
                    <p class="text-lg font-bold text-ink-muted">Vendido no mês</p>
                    <p class="mt-2 text-4xl font-extrabold text-brand-900">R$ {{ number_format($vendidoNoMes, 2, ',', '.') }}</p>
                    <p class="mt-2 text-lg font-bold {{ $lucroNoMes < 0 ? 'text-red-700' : 'text-green-800' }}">
                        Lucro: R$ {{ number_format($lucroNoMes, 2, ',', '.') }}
                    </p>
                    @if ($itensSemCusto > 0)
                        <p class="mt-1 text-base text-ink-muted">
                            {{ $itensSemCusto }} {{ $itensSemCusto === 1 ? 'produto vendido sem preço de custo ficou' : 'produtos vendidos sem preço de custo ficaram' }} fora da conta.
                        </p>
                    @endif
                    <p class="mt-2 text-base font-bold text-brand-700 underline">Ver faturamento</p>
                </x-card>
                </a>

                <a href="{{ route('sales.index') }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    <x-card padding="p-6" class="h-full transition-colors hover:border-brand-700">
                        <p class="text-lg font-bold text-ink-muted">A receber</p>
                        <p class="mt-2 text-4xl font-extrabold text-brand-900">R$ {{ number_format($aReceber, 2, ',', '.') }}</p>
                        <p class="mt-1 text-base text-ink-muted">{{ $contagemAReceber }} {{ $contagemAReceber === 1 ? 'compra pendente' : 'compras pendentes' }}</p>
                    </x-card>
                </a>

                <a href="{{ route('products.index') }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    <x-card padding="p-6" class="h-full transition-colors hover:border-brand-700">
                        <p class="text-lg font-bold text-ink-muted">Estoque baixo</p>
                        <p class="mt-2 text-4xl font-extrabold {{ $estoqueBaixo > 0 ? 'text-red-700' : 'text-brand-900' }}">{{ $estoqueBaixo }}</p>
                        <p class="mt-1 text-base text-ink-muted">{{ $estoqueBaixo === 1 ? 'produto para repor' : 'produtos para repor' }}</p>
                    </x-card>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Registro de Compras -->
                <a href="{{ route('sales.index') }}"
                    class="group relative bg-brand-700 hover:bg-brand-600 transition-all duration-300 aspect-square flex flex-col items-center justify-center p-6 shadow-lg border-4 border-transparent rounded-md hover:border-white/50 lg:hover:scale-105">
                    <div class="text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <span class="text-white text-2xl font-bold text-center">Registro de Compras</span>
                    <div
                        class="absolute bottom-2 right-2 text-white/70 font-bold text-4xl">
                        1</div>
                </a>

                <!-- Estoque de Produtos -->
                <a href="{{ route('products.index') }}"
                    class="group relative bg-accent-700 hover:bg-accent-600 transition-all duration-300 aspect-square flex flex-col items-center justify-center p-6 shadow-lg border-4 border-transparent rounded-md hover:border-white/50 lg:hover:scale-105">
                    <div class="text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-white text-2xl font-bold text-center">Estoque de Produtos</span>
                    <div
                        class="absolute bottom-2 right-2 text-white/70 font-bold text-4xl">
                        2</div>
                </a>

                <!-- Clientes -->
                <a href="{{ route('customers.index') }}"
                    class="group relative bg-accent-700 hover:bg-accent-600 transition-all duration-300 aspect-square flex flex-col items-center justify-center p-6 shadow-lg border-4 border-transparent rounded-md hover:border-white/50 lg:hover:scale-105">
                    <div class="text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-white text-2xl font-bold text-center">Clientes</span>
                    <div
                        class="absolute bottom-2 right-2 text-white/70 font-bold text-4xl">
                        3</div>
                </a>

                <!-- Configurações da Conta -->
                <a href="{{ route('profile.edit') }}"
                    class="group relative bg-brand-700 hover:bg-brand-600 transition-all duration-300 aspect-square flex flex-col items-center justify-center p-6 shadow-lg border-4 border-transparent rounded-md hover:border-white/50 lg:hover:scale-105">
                    <div class="text-white mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-white text-2xl font-bold text-center">Configurações</span>
                    <div
                        class="absolute bottom-2 right-2 text-white/70 font-bold text-4xl">
                        4</div>
                </a>
            </div>
        </div>
    </div>

</x-app-layout>
