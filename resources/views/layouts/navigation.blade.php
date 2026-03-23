@php
    $navDesktopBase = 'rounded-md px-3 py-2 text-sm font-medium transition-colors';
    $navDesktopActive = 'bg-[#002366]/80 text-white';
    $navDesktopInactive = 'text-white/80 hover:bg-white/10 hover:text-white';
    $navMobileBase = 'block rounded-md px-3 py-2 text-base font-medium transition-colors';
    $navMobileActive = 'bg-[#002366]/80 text-white';
    $navMobileInactive = 'text-white/80 hover:bg-white/10 hover:text-white';
@endphp

<nav class="relative bg-[#0047AB]/50 after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:bg-white/10">
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between sm:h-20">
            <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                <!-- Mobile menu button -->
                <button
                    type="button"
                    command="--toggle"
                    commandfor="mobile-menu"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-white/70 hover:bg-white/5 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-sky-300"
                >
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">{{ __('Abrir menu principal') }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 in-aria-expanded:hidden">
                        <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 not-in-aria-expanded:hidden">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                <div class="flex shrink-0 items-center gap-3">
                    <img src="{{ asset('assets/imgs/logo.png') }}" alt="{{ config('app.name', 'System Bar') }}" class="h-9 w-9 rounded-full object-cover sm:h-11 sm:w-11" />
                    <a href="{{ route('dashboard') }}" class="hidden font-bold tracking-wider text-white sm:inline text-lg">
                        SYSTEM BAR
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
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
                    </div>
                </div>
            </div>

            <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                <!-- Profile dropdown -->
                <el-dropdown class="relative ml-3">
                    <button
                        type="button"
                        class="relative flex max-w-[12rem] items-center gap-2 rounded-full cursor-pointer border pr-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-300 sm:max-w-xs"
                    >
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">{{ __('Abrir menu do usuário') }}</span>
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#002366] text-sm font-semibold text-white outline -outline-offset-1 outline-white/10">
                            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden truncate text-sm font-semibold text-white sm:inline">
                            {{ Auth::user()->name }}
                        </span>
                    </button>

                    <el-menu
                        anchor="bottom end"
                        popover
                        class="w-56 origin-top-right rounded-md bg-[#002366] py-1 outline -outline-offset-1 outline-white/10 transition transition-discrete [--anchor-gap:--spacing(2)] data-closed:scale-95 data-closed:transform data-closed:opacity-0 data-enter:duration-100 data-enter:ease-out data-leave:duration-75 data-leave:ease-in"
                    >
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-white/90 focus:bg-white/5 focus:outline-hidden">
                            {{ __('Minha Conta') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-left text-sm text-red-400 focus:bg-white/5 focus:outline-hidden"
                            >
                                {{ __('Sair do Sistema') }}
                            </button>
                        </form>
                    </el-menu>
                </el-dropdown>
            </div>
        </div>
    </div>

    <el-disclosure id="mobile-menu" hidden class="block sm:hidden border-t border-white/10 bg-[#002366]/95">
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
        </div>

        <div class="border-t border-white/10 px-4 py-4">
            <div class="font-semibold text-white">{{ Auth::user()->name }}</div>
            <div class="text-sm text-white/60">{{ Auth::user()->email }}</div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-base font-medium text-white/90 hover:bg-white/10">
                    {{ __('Minha Conta') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="block w-full rounded-md px-3 py-2 text-left text-base font-medium text-red-400 hover:bg-white/10"
                    >
                        {{ __('Sair do Sistema') }}
                    </button>
                </form>
            </div>
        </div>
    </el-disclosure>
</nav>
