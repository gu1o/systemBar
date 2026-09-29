<x-guest-layout>
    <form class="flex flex-col items-start gap-6 self-stretch" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="flex flex-col items-start gap-4 self-stretch">
            <!-- Name -->
            <div class="flex flex-col items-start gap-2 self-stretch">
                <x-input-label for="name" :value="__('Nome')" />
                <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required
                    autofocus autocomplete="name" placeholder="Ex.: João da Silva" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="flex flex-col items-start gap-2 self-stretch">
                <x-input-label for="email" :value="__('E-mail')" />
                <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')"
                    required autocomplete="username" placeholder="Ex.: joao@gmail.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col items-start gap-4 self-stretch">
            <!-- Password -->
            <div class="flex flex-col items-start gap-2 self-stretch">
                <x-input-label for="password" :value="__('Senha')" />

                <p class="text-base text-gray-600">{{ __('A senha deve ter no mínimo 8 caracteres.') }}</p>
                <x-text-input id="password" class="block w-full mt-[-4px]" type="password" name="password" placeholder="********"
                    required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="flex flex-col items-start gap-2 self-stretch">
                <x-input-label for="password_confirmation" :value="__('Confirmar senha')" />

                <x-text-input id="password_confirmation" class="block w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" placeholder="********" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between self-stretch">
            <a class="inline-block text-center text-base text-gray-700 underline underline-offset-4 transition-colors duration-300 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700"
                href="{{ route('login') }}">
                {{ __('Já possui uma conta?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrar') }}
            </x-primary-button>
        </div>

        <a href="{{ route('welcome') }}" title="Voltar para o Início" class="flex items-center justify-center gap-2 self-stretch btn btn-soft group">
            <span class="text-center text-white text-md">
                Voltar para o Início
            </span>

        </a>
    </form>
</x-guest-layout>
