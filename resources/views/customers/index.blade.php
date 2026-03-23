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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
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
                                <td class="flex items-center justify-end gap-4 pr-2.5 py-4 text-right text-lg">
                                    <a class="flex items-center" href="{{ route('customers.edit', $customer) }}">
                                        <svg class="stroke-indigo-600 hover:stroke-indigo-900 transition-all duration-300"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none">
                                            <path
                                                d="M15 6L18 9M13 20H21M5 16L4 20L8 19L19.586 7.414C19.9609 7.03895 20.1716 6.53033 20.1716 6C20.1716 5.46967 19.9609 4.96106 19.586 4.586L19.414 4.414C19.0389 4.03906 18.5303 3.82843 18 3.82843C17.4697 3.82843 16.9611 4.03906 16.586 4.414L5 16Z"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>

                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                    class="flex items-center">
                                        @csrf
                                        @method('DELETE')
                                        <button class="cursor-pointer"
                                            onclick="return confirm('Excluir este cliente?')">
                                            <svg class="stroke-red-600 hover:stroke-red-900" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M4 7H20M10 11V17M14 11V17M5 7L6 19C6 19.5304 6.21071 20.0391 6.58579 20.4142C6.96086 20.7893 7.46957 21 8 21H16C16.5304 21 17.0391 20.7893 17.4142 20.4142C17.7893 20.0391 18 19.5304 18 19L19 7M9 7V4C9 3.73478 9.10536 3.48043 9.29289 3.29289C9.48043 3.10536 9.73478 3 10 3H14C14.2652 3 14.5196 3.10536 14.7071 3.29289C14.8946 3.48043 15 3.73478 15 4V7" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </form>
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
