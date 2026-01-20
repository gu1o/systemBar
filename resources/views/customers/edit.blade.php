<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Cadastrar Novo Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-8">

                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label class="block text-xl font-bold mb-2">Nome</label>
                        <input type="text" name="name" placeholder="Nome"
                            value="{{ old('name', $customer->name) }}" class="w-full rounded border px-4 py-3" required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xl font-bold mb-2">Telefone</label>
                        <input type="text" name="phone" id="phone" class="w-full rounded border px-4 py-3"
                            placeholder="(00) 00000-0000" value="{{ old('phone', $customer->phone) }}">
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
                        <label class="block text-xl font-bold mb-2">Observações</label>
                        <textarea name="notes" placeholder="Observações" class="w-full rounded border px-4 py-3">{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                    <div class="flex justify-between">
                        <button class="bg-[#008080] text-white px-8 py-3 rounded-lg text-xl">
                            Salvar Cliente
                        </button>
                        <a href="{{ route('customers.index') }}" class="text-lg">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
