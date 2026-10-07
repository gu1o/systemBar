<x-app-layout>
    <x-slot name="titulo">Registrar Nova Venda</x-slot>

    <x-slot name="header">
        <x-page-header :titulo="__('Registrar Nova Venda')" />
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card padding="p-5 sm:p-8">
                @php
                    // Sem cliente ou sem produto em estoque os <select> apareciam vazios e a
                    // venda era impossível, sem nenhuma explicação na tela (§A8).
                    $faltando = collect([
                        $customers->isEmpty() ? 'um cliente cadastrado' : null,
                        $products->isEmpty() ? 'um produto com estoque' : null,
                    ])->filter();
                @endphp

                @if ($faltando->isNotEmpty())
                    <x-empty-state
                        :acao="$customers->isEmpty() ? route('customers.create') : route('products.create')"
                        :rotulo="$customers->isEmpty() ? 'Cadastrar um cliente' : 'Cadastrar um produto'">
                        Para registrar uma venda falta {{ $faltando->join(' e ') }}.
                    </x-empty-state>
                @else
                <form action="{{ route('sales.store') }}" method="POST">
                    @csrf

                    <x-form-errors />

                    <div class="mb-8">
                        <label for="customer_id" class="block text-gray-700 text-xl font-bold mb-2">Selecionar Cliente</label>
                        <x-select name="customer_id" id="customer_id" :class="'min-h-12 w-full text-lg'.($errors->has('customer_id') ? ' border-2 border-red-600' : '')" :aria-invalid="$errors->has('customer_id') ? 'true' : null" required>
                            <option value="">Escolha um cliente...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-gray-700 text-xl font-bold mb-4">Produtos da Venda</h3>
                        <div id="items-container">
                            <div class="group grid grid-cols-1 md:grid-cols-[1fr_10rem_auto] gap-4 mb-4 p-4 border border-edge rounded-lg bg-gray-50">
                                <div>
                                    <label for="item-0-produto" class="block text-gray-700 text-base font-bold mb-1">Produto</label>
                                    <x-select name="items[0][product_id]" id="item-0-produto" class="min-h-12 w-full text-lg" required>
                                        <option value="">Selecione um produto...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} - R$ {{ number_format($product->sale_price, 2, ',', '.') }} (Estoque: {{ $product->stock_quantity }})</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div>
                                    <label for="item-0-quantidade" class="block text-gray-700 text-base font-bold mb-1">Quantidade</label>
                                    <input type="number" inputmode="numeric" name="items[0][quantity]" id="item-0-quantidade" min="1" value="1" class="min-h-12 w-full rounded-lg border border-edge bg-surface px-3 text-lg" required>
                                </div>
                                {{-- Some quando só resta um produto: a venda precisa de pelo menos um. --}}
                                <button type="button" data-remover aria-label="Remover este produto"
                                        class="min-h-12 cursor-pointer self-end rounded-lg border-2 border-red-700 px-4 text-base font-bold text-red-700 transition-colors hover:bg-red-50 group-only:hidden">
                                    Remover
                                </button>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="min-h-12 w-full cursor-pointer rounded-lg border-2 border-dashed border-brand-700 px-4 text-lg font-bold text-brand-700 transition-colors hover:bg-brand-100 md:w-auto">+ Adicionar produto</button>
                    </div>

                    {{-- Celular: Finalizar em largura total e Cancelar embaixo, longe do dedo. --}}
                    <div class="flex flex-col gap-3 border-t border-edge pt-6 sm:flex-row sm:items-center sm:justify-between sm:pt-8">
                        <button type="submit" data-rotulo-enviando="Finalizando venda..." class="w-full cursor-pointer bg-brand-700 hover:bg-brand-600 text-white font-bold py-4 px-10 rounded-lg shadow-lg transition-all text-xl sm:w-auto sm:text-2xl">
                            Finalizar Venda
                        </button>
                        <a href="{{ route('sales.index') }}" class="inline-flex min-h-11 items-center justify-center text-gray-600 hover:text-gray-900 font-bold text-lg">
                            Cancelar
                        </a>
                    </div>
                </form>
                @endif
            </x-card>
        </div>
    </div>

    <script>
        let itemIndex = 1;
        // ?. — sem cliente ou produto o formulário não é renderizado (§A8), e o botão não existe.
        // Copia o primeiro item (em vez de repetir o HTML aqui) e troca o índice 0 pelo novo.
        document.getElementById('add-item')?.addEventListener('click', function() {
            const container = document.getElementById('items-container');
            // Modelo: um item que não está saindo (esse vem com campos desligados).
            const newItem = container.querySelector(':scope > :not([data-saindo])').cloneNode(true);
            newItem.classList.add('animate-entra');
            newItem.querySelectorAll('[name], [id], [for]').forEach(el => {
                for (const attr of ['name', 'id', 'for']) {
                    if (el.hasAttribute(attr)) el.setAttribute(attr, el.getAttribute(attr).replace(/\d+/, itemIndex));
                }
            });
            newItem.querySelector('select').value = '';
            newItem.querySelector('input').value = 1;
            container.appendChild(newItem);
            itemIndex++;
        });

        // Os índices podem ficar com buracos (items[0], items[2]): o controller não depende da sequência.
        // Saída em "puff": cresce, desfoca e some; depois o espaço fecha, sem os itens de baixo pularem.
        document.getElementById('items-container')?.addEventListener('click', async function(e) {
            const botao = e.target.closest('[data-remover]');
            if (!botao || this.querySelectorAll(':scope > :not([data-saindo])').length < 2) return;

            const item = botao.parentElement;
            item.dataset.saindo = '';
            // Desligados não vão no envio nem barram o "required" enquanto a animação roda.
            item.querySelectorAll('select, input, button').forEach(el => el.disabled = true);
            document.getElementById('add-item').focus(); // o foco não some junto com o botão

            if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
                await item.animate(
                    [{}, { opacity: 0, transform: 'scale(1.1)', filter: 'blur(6px)' }],
                    { duration: 250, easing: 'ease-in', fill: 'forwards' },
                ).finished;
                item.style.overflow = 'hidden';
                await item.animate(
                    [{ height: item.offsetHeight + 'px' }, { height: 0, paddingBlock: 0, marginBottom: 0, borderWidth: 0 }],
                    { duration: 200, easing: 'ease-out' },
                ).finished;
            }
            item.remove();
        });
    </script>
</x-app-layout>
