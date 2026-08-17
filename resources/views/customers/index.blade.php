<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Clientes') }}
            </h2>
            <a href="{{ route('customers.create') }}"
                class="bg-[#008080] hover:bg-[#00A0A0] text-white font-bold py-2 px-4 rounded-lg shadow-md transition-all">
                + Novo Cliente
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Nome</th>
                            <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Telefone</th>
                            <th class="px-6 py-3 text-right text-base font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-lg font-medium text-gray-900">
                                    {{ $customer->name }}
                                </td>
                                <td class="px-6 py-4 text-lg text-gray-600">
                                    {{ $customer->phone ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <a href="{{ route('customers.edit', $customer) }}"
                                            class="inline-flex min-h-11 items-center rounded-lg border-2 border-[#0047AB] px-5 py-3 text-base font-bold text-[#0047AB] transition-colors hover:bg-[#0047AB] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0047AB]">
                                            Editar
                                        </a>

                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border-2 border-red-700 px-5 py-3 text-base font-bold text-red-700 transition-colors hover:bg-red-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700"
                                                onclick="return confirm('Excluir o cliente ' + @js($customer->name) + '? Esta ação não pode ser desfeita.')">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-6">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
