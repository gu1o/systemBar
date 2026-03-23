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
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')"
                    required autocomplete="username" placeholder="Ex.: joao@gmail.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col items-start gap-4 self-stretch">
            <!-- Password -->
            <div class="flex flex-col items-start gap-2 self-stretch">
                <x-input-label for="password" :value="__('Senha')" />

                <p class="text-xs text-gray-500">{{ __('A senha deve ter no mínimo 8 caracteres.') }}</p>
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
            <a class="relative inline-block text-center text-sm text-gray-600 transition-colors duration-300 hover:text-[#0047AB] cursor-pointer after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:origin-left after:scale-x-0 after:bg-current after:transition-transform after:duration-300 after:content-[''] hover:after:scale-x-100"
                href="{{ route('login') }}">
                {{ __('Já possui uma conta?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrar') }}
            </x-primary-button>
        </div>

        <a href="{{ route('welcome') }}" title="Voltar para o Início" class="flex items-center justify-center gap-2 self-stretch btn btn-soft group">
            <span class="text-center text-white/80 group-hover:text-white transition-all duration-300 text-md">
                Voltar para o Início
            </span>

            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M15 11.5H3M3 11.5L7.5 7M3 11.5L7.5 16" class="stroke-white/80 group-hover:stroke-white transition-all duration-300" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10 16V19C10 19.5523 10.4477 20 11 20H20C20.5523 20 21 19.5523 21 19V4C21 3.44772 20.5523 3 20 3H11C10.4477 3 10 3.44772 10 4V7" class="stroke-white/80 group-hover:stroke-white transition-all duration-300" stroke-width="2" stroke-linecap="round"/>
              </svg>
        </a>
    </form>
</x-guest-layout>
