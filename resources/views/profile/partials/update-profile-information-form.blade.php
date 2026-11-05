<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informações do Perfil') }}
        </h2>

        <p class="mt-1 text-base text-gray-700">
            {{ __("Atualize as informações do seu perfil.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            {{-- Só leitura: o e-mail identifica a conta e não é alterado por aqui. --}}
            <x-text-input id="email" type="email" class="mt-1 block w-full cursor-not-allowed bg-gray-100 text-gray-600" :value="$user->email" disabled aria-describedby="email-ajuda" />
            <p id="email-ajuda" class="mt-1 text-base text-gray-600">{{ __('O e-mail não pode ser alterado.') }}</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>
        </div>
    </form>
</section>
