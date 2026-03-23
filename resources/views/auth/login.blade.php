<x-guest-layout>
    <div class="flex flex-col items-center self-stretch gap-2 mb-6">
        <h2 class="text-3xl font-bold text-[#002366]">Bem-vindo de volta!</h2>
        <p class="text-center w-[300px] text-gray-500 text-sm">Acesse sua conta para gerenciar seu comércio.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form class="flex flex-col items-start gap-6 self-stretch" method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="flex flex-col items-start gap-2 self-stretch">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block w-full"
                type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                placeholder="Ex.: joao@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col items-end gap-3 self-stretch">
            <!-- Password -->
            <div class="flex flex-col items-start gap-2 self-stretch">
                <x-input-label for="password" :value="__('Senha')" />
                <x-text-input id="password" class="block w-full"
                    type="password" name="password" required autocomplete="current-password" placeholder="********" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            @if (Route::has('password.request'))
                <a class="relative inline-block text-center text-sm text-gray-600 transition-colors duration-300 hover:text-[#0047AB] cursor-pointer after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:origin-left after:scale-x-0 after:bg-current after:transition-transform after:duration-300 after:content-[''] hover:after:scale-x-100"
                    href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            @endif
        </div>

        <div class="flex flex-col gap-4 self-stretch">
            <button type="submit"
                class="btn-brand w-full border border-[#0047AB] py-4 rounded-md shadow-lg text-base">
                Entrar no Sistema
            </button>

            <a href="{{ route('welcome') }}" title="Voltar para o Início" class="flex items-center justify-center gap-2 self-stretch btn btn-soft group">
                <span class="text-center text-gray-600 group-hover:text-[#0047AB] transition-all duration-300 text-md">
                    Voltar para o Início
                </span>
    
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M15 11.5H3M3 11.5L7.5 7M3 11.5L7.5 16" class="stroke-gray-600 group-hover:stroke-[#0047AB] transition-all duration-300" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 16V19C10 19.5523 10.4477 20 11 20H20C20.5523 20 21 19.5523 21 19V4C21 3.44772 20.5523 3 20 3H11C10.4477 3 10 3.44772 10 4V7" class="stroke-gray-600 group-hover:stroke-[#0047AB] transition-all duration-300" stroke-width="2" stroke-linecap="round"/>
                  </svg>
            </a>
        </div>
    </form>
</x-guest-layout>
