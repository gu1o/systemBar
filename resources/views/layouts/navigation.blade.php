@php
    $navDesktopBase = 'rounded-md px-3 py-2 text-base font-medium transition-colors';
    $navDesktopActive = 'bg-white/15 text-white';
    $navDesktopInactive = 'text-white/80 hover:bg-white/10 hover:text-white';
    $navMobileBase = 'block rounded-md px-3 py-3 text-lg font-medium transition-colors';
    $navMobileActive = 'bg-white/15 text-white';
    $navMobileInactive = 'text-white/80 hover:bg-white/10 hover:text-white';
@endphp

{{-- Navegação em Alpine (§V6).

     Antes dependia de <el-dropdown>/<el-disclosure> do @tailwindplus/elements,
     carregado de um CDN sem versão fixa e fora do package.json. Se o CDN caísse,
     o celular ficava sem menu e sem jeito de sair do sistema — dependência de rede
     em runtime no caminho crítico. Alpine já está no bundle e já move a x-modal. --}}
<nav x-data="{ menu: false, perfil: false }"
     class="relative bg-brand-900 after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:bg-white/10">
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between md:h-20">
            <div class="absolute inset-y-0 left-0 flex items-center md:hidden">
                <button
                    type="button"
                    x-on:click="menu = ! menu"
                    :aria-expanded="menu"
                    aria-controls="mobile-menu"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-white/70 hover:bg-white/5 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-sky-300"
                >
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">{{ __('Abrir menu principal') }}</span>
                    <svg x-show="! menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="size-6">
                        <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <svg x-show="menu" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="size-6">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-1 items-center justify-center md:items-stretch md:justify-start">
                <div class="flex shrink-0 items-center gap-3">
                    <img src="{{ asset('assets/imgs/logo.svg') }}" alt="{{ config('app.name', 'System Bar') }}" class="h-9 w-9 rounded-full object-cover md:h-11 md:w-11" />
                    <a href="{{ route('dashboard') }}" class="hidden font-bold tracking-wider text-white md:inline text-lg">
                        SYSTEM BAR
                    </a>
                </div>
                <div class="hidden md:ml-6 md:flex md:items-center">
                    <div class="flex space-x-4">
                        <a
                            href="{{ route('dashboard') }}"
                            @class([$navDesktopBase, request()->routeIs('dashboard') ? $navDesktopActive : $navDesktopInactive])
                            @if(request()->routeIs('dashboard')) aria-current="page" @endif
                        >
                            {{ __('Início') }}
                        </a>
                        <a
                            href="{{ route('sales.index') }}"
                            @class([$navDesktopBase, request()->routeIs('sales.*') ? $navDesktopActive : $navDesktopInactive])
                            @if(request()->routeIs('sales.*')) aria-current="page" @endif
                        >
                            {{ __('Vendas') }}
                        </a>
                        <a
                            href="{{ route('products.index') }}"
                            @class([$navDesktopBase, request()->routeIs('products.*') ? $navDesktopActive : $navDesktopInactive])
                            @if(request()->routeIs('products.*')) aria-current="page" @endif
                        >
                            {{ __('Estoque') }}
                        </a>
                        <a
                            href="{{ route('customers.index') }}"
                            @class([$navDesktopBase, request()->routeIs('customers.*') ? $navDesktopActive : $navDesktopInactive])
                            @if(request()->routeIs('customers.*')) aria-current="page" @endif
                        >
                            {{ __('Clientes') }}
                        </a>
                        <a
                            href="{{ route('relatorio') }}"
                            @class([$navDesktopBase, request()->routeIs('relatorio') ? $navDesktopActive : $navDesktopInactive])
                            @if(request()->routeIs('relatorio')) aria-current="page" @endif
                        >
                            {{ __('Faturamento') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="absolute inset-y-0 right-0 flex items-center pr-2 md:static md:inset-auto md:ml-6 md:pr-0">
                <div class="relative ml-3"
                     x-on:click.outside="perfil = false"
                     x-on:keydown.escape.window="perfil = false">
                    <button
                        type="button"
                        x-on:click="perfil = ! perfil"
                        :aria-expanded="perfil"
                        aria-haspopup="true"
                        class="relative flex max-w-[12rem] items-center gap-2 rounded-full cursor-pointer border md:pr-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-300 md:max-w-xs"
                    >
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">{{ __('Abrir menu do usuário') }}</span>
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-base font-semibold text-white outline -outline-offset-1 outline-white/10">
                            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden truncate text-base font-semibold text-white md:inline">
                            {{ Auth::user()->name }}
                        </span>
                    </button>

                    <div
                        x-show="perfil"
                        x-cloak
                        x-transition.origin.top.right
                        class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-brand-900 py-1 outline -outline-offset-1 outline-white/10"
                    >
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-base text-white/90 hover:bg-white/5 focus-visible:bg-white/5 focus-visible:outline-hidden">
                            {{ __('Minha Conta') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit" data-rotulo-enviando="Saindo..."
                                class="block w-full cursor-pointer px-4 py-3 text-left text-base font-semibold text-red-300 hover:bg-white/5 focus-visible:bg-white/5 focus-visible:outline-hidden"
                            >
                                {{ __('Sair do Sistema') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="mobile-menu" x-show="menu" x-cloak class="block md:hidden border-t border-white/10 bg-brand-900">
        <div class="space-y-1 px-2 pt-2 pb-3">
            <a
                href="{{ route('dashboard') }}"
                @class([$navMobileBase, request()->routeIs('dashboard') ? $navMobileActive : $navMobileInactive])
                @if(request()->routeIs('dashboard')) aria-current="page" @endif
            >
                {{ __('Início') }}
            </a>
            <a
                href="{{ route('sales.index') }}"
                @class([$navMobileBase, request()->routeIs('sales.*') ? $navMobileActive : $navMobileInactive])
                @if(request()->routeIs('sales.*')) aria-current="page" @endif
            >
                {{ __('Vendas') }}
            </a>
            <a
                href="{{ route('products.index') }}"
                @class([$navMobileBase, request()->routeIs('products.*') ? $navMobileActive : $navMobileInactive])
                @if(request()->routeIs('products.*')) aria-current="page" @endif
            >
                {{ __('Estoque') }}
            </a>
            <a
                href="{{ route('customers.index') }}"
                @class([$navMobileBase, request()->routeIs('customers.*') ? $navMobileActive : $navMobileInactive])
                @if(request()->routeIs('customers.*')) aria-current="page" @endif
            >
                {{ __('Clientes') }}
            </a>
            <a
                href="{{ route('relatorio') }}"
                @class([$navMobileBase, request()->routeIs('relatorio') ? $navMobileActive : $navMobileInactive])
                @if(request()->routeIs('relatorio')) aria-current="page" @endif
            >
                {{ __('Faturamento') }}
            </a>

            {{-- Sair é das poucas ações que este público procura ativamente, e no celular
                 só existia dentro do dropdown de perfil (§A11). --}}
            <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-2 mt-2">
                @csrf
                <button type="submit" data-rotulo-enviando="Saindo..."
                        class="block w-full cursor-pointer rounded-md px-3 py-3 text-left text-lg font-bold text-red-300 hover:bg-white/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-300">
                    {{ __('Sair do Sistema') }}
                </button>
            </form>
        </div>
    </div>
</nav>
