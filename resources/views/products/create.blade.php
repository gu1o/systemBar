<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Cadastrar Novo Produto') }}
        </h2>
    </x-slot>

    <script>
        const brlCurrencyMask = (e) => {
            const {
                value
            } = e.target;
            let mask = "";
            mask = value.replace(",", "").replace(".", "").replace(/\D/g, "");

            const options = {
                minimumFractionDigits: 2
            };
            const result = new Intl.NumberFormat("pt-BR", options).format(
                parseFloat(mask) / 100,
            );

            if (result === "NaN") {
                e.target.value = "";
                return;
            }

            e.target.value = result;
        };
    </script>

    <div class="py-12 bg-[#002366] min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 rounded-lg border border-red-400 bg-red-50 p-4 text-red-800" role="alert">
                            <p class="font-bold">Corrija os campos abaixo:</p>
                            <ul class="mt-2 list-inside list-disc text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 text-xl font-bold mb-2">Nome do Produto</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                            placeholder="Coca Cola 2L" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="cost_price" class="block text-gray-700 text-xl font-bold mb-2">Preço de Custo
                                (R$) <span class="text-base font-normal text-gray-500">(opcional)</span></label>
                            <div class="relative">
                                
                                <input type="checkbox" id="cost_price_info_toggle" class="peer sr-only" tabindex="-1">
                                <input type="text" name="cost_price" id="cost_price" value="{{ old('cost_price') }}"
                                    class="shadow appearance-none border rounded w-full py-3 pl-4 pr-12 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                                    placeholder="0,00" data-mask="0000000.000" oninput="brlCurrencyMask(event)">
                                <label for="cost_price_info_toggle"
                                    class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center text-gray-500 hover:text-gray-700"
                                    title="{{ __('Informação sobre preço de custo') }}"
                                    aria-label="{{ __('Mostrar informação sobre preço de custo') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" aria-hidden="true">
                                        <path
                                            d="M12 13V16M12 8V11M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </label>
                                <div
                                    class="pointer-events-none invisible absolute left-0 right-0 top-full z-10 mt-1 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-600 shadow-md opacity-0 transition-[opacity,visibility] duration-150 peer-checked:pointer-events-auto peer-checked:visible peer-checked:opacity-100">
                                    {{ __('Informe o valor que você paga pelo produto ou valor de custo para fabricação em casos de produtos caseiros. Use o mesmo formato do caixa (ex.: 12,50).
                                    ') . ' ' . __('Campo não obrigatório.') }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="sale_price" class="block text-gray-700 text-xl font-bold mb-2">Preço de Venda
                                (R$)</label>
                            <input type="text" name="sale_price" id="sale_price" value="{{ old('sale_price') }}"
                                class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                                placeholder="0,00" data-mask="0000000.000" required oninput="brlCurrencyMask(event)">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="stock_quantity" class="block text-gray-700 text-xl font-bold mb-2">Quantidade em
                            Estoque</label>
                        <input type="number" name="stock_quantity" id="stock_quantity"
                            value="{{ old('stock_quantity') }}"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-lg placeholder:text-gray-400"
                            required min="0" placeholder="10">
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('products.index') }}"
                            class="text-gray-600 hover:text-red-500 font-bold text-lg transtition-colors duration-300">
                            Cancelar
                        </a>
                        <x-button-submit>
                            Salvar Produto
                        </x-button-submit>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
