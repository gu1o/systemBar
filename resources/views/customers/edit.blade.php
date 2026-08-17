<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-8">

                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <x-form-errors />

                    <div class="mb-6">
                        <label for="name" class="block text-xl font-bold mb-2">Nome</label>
                        <input type="text" name="name" id="name" placeholder="Nome"
                            value="{{ old('name', $customer->name) }}"
                            class="w-full rounded border px-4 py-3 text-lg @error('name') border-2 border-red-600 @enderror"
                            @error('name') aria-invalid="true" @enderror required>
                    </div>

                    <div class="mb-6">
                        <label for="phone" class="block text-xl font-bold mb-2">Telefone</label>
                        <input type="text" name="phone" id="phone"
                            class="w-full rounded border px-4 py-3 text-lg @error('phone') border-2 border-red-600 @enderror"
                            @error('phone') aria-invalid="true" @enderror placeholder="(00) 00000-0000"
                            value="{{ old('phone', $customer->phone) }}">
                    </div>

                    <script>
                        document.getElementById('phone').addEventListener('input', function(e) {
                            let value = e.target.value.replace(/\D/g, '');
                            let formatted = '';

                            if (value.length > 0) {
                                formatted = '(' + value.substring(0, 2);
                            }
                            if (value.length > 2) {
                                formatted += ') ' + value.substring(2, 7);
                            }
                            if (value.length > 7) {
                                formatted += '-' + value.substring(7, 11);
                            }

                            e.target.value = formatted;
                        });
                    </script>

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
                        <x-button-submit>
                            Atualizar Cliente
                        </x-button-submit>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
