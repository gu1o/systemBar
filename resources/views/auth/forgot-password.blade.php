<x-guest-layout>
    <div class="mb-4 text-base text-gray-700">
        {{ __('Esqueceu sua senha? Sem problema. Apenas nos informe seu endereço de email e enviaremos um link para redefinir a senha que permitirá que você escolha uma nova.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus placeholder="Ex.: joao@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col items-center mt-4 gap-4">
            <x-primary-button class="w-full">
                {{ __('Enviar link de redefinição de senha') }}
            </x-primary-button>
            
            <a href="{{ route('login') }}" title="Voltar para o Login"
                class="flex items-center justify-center gap-2 self-stretch btn btn-soft group">
                <span class="text-center text-white text-md">
                    Voltar para o Login
                </span>

            </a>
        </div>
    </form>
</x-guest-layout>
