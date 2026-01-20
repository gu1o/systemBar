<x-guest-layout>
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-[#002366]">Bem-vindo de volta!</h2>
        <p class="text-gray-500 text-lg">Acesse sua conta para gerenciar seu comércio.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-6">
            <label for="email" class="block text-gray-700 text-lg font-bold mb-2">Seu E-mail</label>
            <input id="email" class="block w-full px-4 py-3 rounded-lg border-gray-300 focus:border-[#0047AB] focus:ring-[#0047AB] text-lg" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-lg font-bold mb-2">Sua Senha</label>
            <input id="password" class="block w-full px-4 py-3 rounded-lg border-gray-300 focus:border-[#0047AB] focus:ring-[#0047AB] text-lg"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-4">
            <button type="submit" class="w-full bg-[#0047AB] hover:bg-[#0056D2] text-white font-bold py-4 rounded-xl shadow-lg transition-all text-xl">
                Entrar no Sistema
            </button>

            @if (Route::has('password.request'))
                <a class="text-center text-gray-600 hover:text-[#0047AB] text-lg underline" href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            @endif
        </div>

        <div class="flex items-center justify-center mt-8">
            <a class="text-center text-gray-600 hover:text-[#0047AB] text-lg underline" href="/">Voltar para o Início</a>
        </div>
    </form>
</x-guest-layout>
