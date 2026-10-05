<x-app-layout>
    <x-slot name="titulo">Editar Cliente</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Editar Cliente')" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card padding="p-8">

                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <x-form-errors />

                    <div class="mb-6">
                        <label for="name" class="block text-xl font-bold mb-2">Nome</label>
                        <input type="text" autocomplete="name" name="name" id="name" placeholder="Nome"
                            value="{{ old('name', $customer->name) }}"
                            class="w-full rounded border px-4 py-3 text-lg @error('name') border-2 border-red-600 @enderror"
                            @error('name') aria-invalid="true" @enderror required>
                    </div>

                    <div class="mb-6">
                        <label for="phone" class="block text-xl font-bold mb-2">Telefone</label>
                        <input type="tel" inputmode="numeric" autocomplete="tel" data-mask-telefone
                            aria-describedby="phone-ajuda" name="phone" id="phone"
                            class="w-full rounded border px-4 py-3 text-lg @error('phone') border-2 border-red-600 @enderror"
                            @error('phone') aria-invalid="true" @enderror placeholder="(00) 00000-0000"
                            value="{{ old('phone', $customer->phone) }}">
                        <p id="phone-ajuda" class="mt-1 text-base text-gray-600">Com DDD, só números. Exemplo: (11) 98765-4321</p>
                    </div>

                    <div class="mb-8">
                        <label for="notes" class="block text-xl font-bold mb-2">Observações</label>
                        <textarea name="notes" id="notes" placeholder="Observações"
                            class="w-full rounded border px-4 py-3 text-lg @error('notes') border-2 border-red-600 @enderror"
                            @error('notes') aria-invalid="true" @enderror>{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('customers.index') }}"
                            class="text-gray-600 hover:text-red-500 font-bold text-lg transition-colors duration-300">
                            Cancelar
                        </a>
                        <x-button-submit data-rotulo-enviando="Atualizando...">
                            Atualizar Cliente
                        </x-button-submit>
                    </div>
                </form>

                <x-phone-mask />

            </x-card>
        </div>
    </div>
</x-app-layout>
