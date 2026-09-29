<x-guest-layout>
    <div class="flex flex-col items-center self-stretch gap-2 mb-6">
        <h2 class="text-3xl font-bold text-brand-900">Bem-vindo de volta!</h2>
        <p class="text-center w-[300px] text-gray-700 text-base">Acesse sua conta para gerenciar seu comércio.</p>
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
                <a class="inline-block text-center text-base text-gray-700 underline underline-offset-4 transition-colors duration-300 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700"
                    href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            @endif
        </div>

        <div class="flex flex-col gap-4 self-stretch">
            <button type="submit"
                class="btn-brand w-full border border-brand-700 py-4 rounded-md shadow-lg text-base">
                Entrar no Sistema
            </button>

            <a href="{{ route('welcome') }}" title="Voltar para o Início" class="flex items-center justify-center gap-2 self-stretch btn btn-soft group">
                <span class="text-center text-white text-md">
                    Voltar para o Início
                </span>

            </a>
        </div>
    </form>
</x-guest-layout>
