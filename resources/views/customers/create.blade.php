<x-app-layout>
    <x-slot name="titulo">Cadastrar Cliente</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Cadastrar Novo Cliente')" />
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card padding="p-5 sm:p-8">

                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    <x-form-errors />

                    <div class="mb-6">
                        <label for="name" class="block text-xl font-bold mb-2">Nome</label>
                        <input type="text" autocomplete="name" name="name" id="name" value="{{ old('name') }}" placeholder="Nome"
                            class="w-full rounded border px-4 py-3 text-lg @error('name') border-2 border-red-600 @enderror"
                            @error('name') aria-invalid="true" @enderror required>
                    </div>

                    <div class="mb-6">
                        <label for="phone" class="block text-xl font-bold mb-2">Telefone</label>
                        <input type="tel" inputmode="numeric" autocomplete="tel" data-mask-telefone
                            aria-describedby="phone-ajuda" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full rounded border px-4 py-3 text-lg @error('phone') border-2 border-red-600 @enderror"
                            @error('phone') aria-invalid="true" @enderror placeholder="(00) 00000-0000">
                        <p id="phone-ajuda" class="mt-1 text-base text-gray-600">Com DDD, só números. Exemplo: (11) 98765-4321</p>
                    </div>

                    <div class="mb-8">
                        <label for="notes" class="block text-xl font-bold mb-2">Observações</label>
                        <textarea name="notes" id="notes" placeholder="Observações"
                            class="w-full rounded border px-4 py-3 text-lg @error('notes') border-2 border-red-600 @enderror"
                            @error('notes') aria-invalid="true" @enderror>{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-edge pt-6 sm:flex-row sm:items-center sm:justify-between sm:pt-8">
                        <a href="{{ route('customers.index') }}"
                            class="inline-flex min-h-11 items-center justify-center text-gray-600 hover:text-red-500 font-bold text-lg transition-colors duration-300">
                            Cancelar
                        </a>
                        <x-button-submit class="w-full sm:w-auto">
                            Salvar Cliente
                        </x-button-submit>
                    </div>
                </form>

                <x-phone-mask />

            </x-card>
        </div>
    </div>
</x-app-layout>
