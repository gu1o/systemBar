<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Esqueceu sua senha? Sem problema. Apenas nos informe seu endereço de email e enviaremos um link para redefinir a senha que permitirá que você escolha uma nova.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus placeholder="Ex.: joao@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col items-center mt-4 gap-4">
            <x-primary-button class="w-full justify-center rounded-sm py-2 px-4 h-10">
                {{ __('Enviar link de redefinição de senha') }}
            </x-primary-button>
            
            <a href="{{ route('login') }}" title="Voltar para o Login"
                class="flex items-center justify-center gap-2 self-stretch btn btn-soft group">
                <span class="text-center text-white/80 group-hover:text-white transition-all duration-300 text-md">
                    Voltar para o Login
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none">
                    <path d="M15 11.5H3M3 11.5L7.5 7M3 11.5L7.5 16"
                        class="stroke-white/80 group-hover:stroke-white transition-all duration-300" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                    <path
                        d="M10 16V19C10 19.5523 10.4477 20 11 20H20C20.5523 20 21 19.5523 21 19V4C21 3.44772 20.5523 3 20 3H11C10.4477 3 10 3.44772 10 4V7"
                        class="stroke-white/80 group-hover:stroke-white transition-all duration-300" stroke-width="2"
                        stroke-linecap="round" />
                </svg>
            </a>
        </div>
    </form>
</x-guest-layout>
