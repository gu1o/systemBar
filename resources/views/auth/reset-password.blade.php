<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Identifica a conta pelo hash que veio no link; não é exibido. --}}
        <input type="hidden" name="email" value="{{ $request->email }}">

        <h1 class="text-2xl font-bold text-gray-900">Alterar senha</h1>
        <p class="mt-2 text-base text-gray-600">Escolha uma nova senha para acessar sua conta.</p>

        {{-- Link vencido/inválido chega aqui como erro de "email". --}}
        <x-input-error :messages="$errors->get('email')" class="mt-4" />

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" :value="__('Nova senha')" />
            <p class="mt-1 text-base text-gray-600">{{ __('A senha deve ter no mínimo 8 caracteres.') }}</p>
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar nova senha')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Salvar nova senha') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
