<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Obrigado por se cadastrar! Antes de começar, confirme seu endereço de e-mail clicando no link que acabamos de enviar para você. Se não recebeu o e-mail, podemos enviar outro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('Um novo link de confirmação foi enviado para o e-mail informado no cadastro.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Reenviar e-mail de confirmação') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Sair do Sistema') }}
            </button>
        </form>
    </div>
</x-guest-layout>
