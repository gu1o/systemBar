@php
    $reais = fn ($valor) => 'R$ '.number_format($valor, 2, ',', '.');

    // Botão de ação dos cartões de resumo: tom claro da marca, enche no hover.
    $botaoSuave = 'mt-auto inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand-700/10 px-4 py-2 text-base font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700';

    // Selo só quando há um número para mostrar: selo fixo ("Mais usado") vira ruído.
    $atalhos = [
        ['rota' => route('sales.index'), 'icone' => 'elemplus-list', 'cor' => 'bg-brand-700 hover:bg-brand-600',
         'titulo' => 'Registro de Compras', 'texto' => 'Registrar uma venda, ver as compras e marcar como pago.',
         'selo' => $comprasHoje > 0 ? $comprasHoje.' '.($comprasHoje === 1 ? 'venda hoje' : 'vendas hoje') : null],
        ['rota' => route('products.index'), 'icone' => 'elemplus-box', 'cor' => 'bg-accent-700 hover:bg-accent-600',
         'titulo' => 'Estoque de Produtos', 'texto' => 'Consultar preços, repor o estoque e cadastrar produtos.',
         'selo' => $estoqueBaixo > 0 ? $estoqueBaixo.' '.($estoqueBaixo === 1 ? 'item acabando' : 'itens acabando') : null, 'alerta' => true],
        ['rota' => route('customers.index'), 'icone' => 'elemplus-user', 'cor' => 'bg-accent-700 hover:bg-accent-600',
         'titulo' => 'Clientes', 'texto' => 'Cadastrar clientes e consultar os contatos.',
         'selo' => null],
        ['rota' => route('profile.edit'), 'icone' => 'elemplus-setting', 'cor' => 'bg-brand-700 hover:bg-brand-600',
         'titulo' => 'Configurações', 'texto' => 'Seus dados, sua senha e o acesso à conta.',
         'selo' => null],
    ];
@endphp

<x-app-layout>
    <x-slot name="titulo">Painel de Controle</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            {{-- Abertura no lugar da faixa azul: o painel é a porta de entrada, ganha boas-vindas. --}}
            <x-card padding="p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-md bg-brand-700/10 px-3 py-1 text-sm font-bold uppercase tracking-wide text-brand-700">Painel principal</span>
                    <span class="inline-flex items-center gap-1 text-base text-ink-muted">
                        <x-elemplus-calendar class="size-4" aria-hidden="true" />
                        {{ \Illuminate\Support\Str::ucfirst(now()->translatedFormat('l, d \d\e F')) }}
                    </span>
                </div>
                <h1 class="mt-3 text-4xl font-extrabold text-brand-900">Painel de Controle</h1>
                <p class="mt-2 text-lg text-ink-muted">Olá, {{ auth()->user()->name }}! Aqui está o resumo das vendas e o acesso rápido ao seu comércio.</p>
            </x-card>

            {{-- §F1 — os quatro números que o dono do comércio hoje descobre somando
                 na calculadora. "A receber" é o mais útil: é dinheiro na rua. --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <x-card padding="p-6" class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg bg-brand-700/10 p-2 text-brand-700"><x-elemplus-coin class="size-6" aria-hidden="true" /></span>
                            <p class="text-lg font-bold text-ink">Vendido hoje</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-brand-900">{{ $reais($vendidoHoje) }}</p>
                        <p class="mt-1 text-base text-ink-muted">{{ $comprasHoje }} {{ $comprasHoje === 1 ? 'compra' : 'compras' }} hoje</p>
                    </div>
                    <a href="{{ route('sales.create') }}" class="{{ $botaoSuave }}">
                        Nova venda <x-elemplus-right class="size-5" aria-hidden="true" />
                    </a>
                </x-card>

                <x-card padding="p-6" class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg bg-brand-700/10 p-2 text-brand-700"><x-elemplus-data-line class="size-6" aria-hidden="true" /></span>
                            <p class="text-lg font-bold text-ink">Vendido no mês</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-brand-900">{{ $reais($vendidoNoMes) }}</p>
                        <p class="mt-1 text-lg font-bold {{ $lucroNoMes < 0 ? 'text-red-700' : 'text-green-800' }}">Lucro: {{ $reais($lucroNoMes) }}</p>
                        @if ($itensSemCusto > 0)
                            <p class="mt-1 text-base text-ink-muted">
                                {{ $itensSemCusto }} {{ $itensSemCusto === 1 ? 'produto vendido sem preço de custo ficou' : 'produtos vendidos sem preço de custo ficaram' }} fora da conta.
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('relatorio') }}" class="{{ $botaoSuave }}">
                        Ver faturamento <x-elemplus-right class="size-5" aria-hidden="true" />
                    </a>
                </x-card>

                <x-card padding="p-6" class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg bg-brand-700/10 p-2 text-brand-700"><x-elemplus-clock class="size-6" aria-hidden="true" /></span>
                            <p class="text-lg font-bold text-ink">A receber</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-brand-900">{{ $reais($aReceber) }}</p>
                        <p class="mt-1 text-base text-ink-muted">{{ $contagemAReceber }} {{ $contagemAReceber === 1 ? 'compra pendente' : 'compras pendentes' }}</p>
                    </div>
                    <a href="{{ route('sales.index', ['situacao' => 'pending']) }}" class="{{ $botaoSuave }}">
                        Ver quem deve <x-elemplus-right class="size-5" aria-hidden="true" />
                    </a>
                </x-card>

                <x-card padding="p-6" class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg p-2 {{ $estoqueBaixo > 0 ? 'bg-red-50 text-red-700' : 'bg-brand-700/10 text-brand-700' }}"><x-elemplus-warning class="size-6" aria-hidden="true" /></span>
                            <p class="text-lg font-bold text-ink">Estoque baixo</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold {{ $estoqueBaixo > 0 ? 'text-red-700' : 'text-brand-900' }}">
                            {{ $estoqueBaixo }} <span class="text-lg font-bold text-ink">{{ $estoqueBaixo === 1 ? 'produto para repor' : 'produtos para repor' }}</span>
                        </p>
                        @if ($maisBaixo)
                            <p class="mt-1 text-base text-ink-muted">
                                {{ $maisBaixo->name }}: {{ $maisBaixo->stock_quantity === 1 ? 'resta' : 'restam' }} {{ $maisBaixo->stock_quantity }}
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('products.index') }}" class="{{ $botaoSuave }}">
                        {{ $estoqueBaixo > 0 ? 'Repor estoque' : 'Ver estoque' }} <x-elemplus-right class="size-5" aria-hidden="true" />
                    </a>
                </x-card>
            </div>

            <section>
                <h2 class="text-2xl font-extrabold text-ink">O que você deseja fazer agora?</h2>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($atalhos as $numero => $atalho)
                        <a href="{{ $atalho['rota'] }}"
                           class="group relative flex min-h-64 flex-col rounded-card p-6 text-white shadow-lg transition-all duration-300 lg:hover:scale-105 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 {{ $atalho['cor'] }}">
                            <span class="w-fit rounded-xl bg-white/15 p-3"><x-dynamic-component :component="$atalho['icone']" class="size-10" aria-hidden="true" /></span>
                            <span class="absolute right-6 top-4 text-5xl font-extrabold text-white" aria-hidden="true">{{ $numero + 1 }}</span>

                            <div class="mt-auto pt-6">
                                @if ($atalho['selo'])
                                    <span class="mb-2 inline-block rounded bg-white px-2 py-0.5 text-sm font-extrabold uppercase {{ ($atalho['alerta'] ?? false) ? 'text-red-700' : 'text-brand-900' }}">{{ $atalho['selo'] }}</span>
                                @endif
                                <span class="block text-2xl font-extrabold">{{ $atalho['titulo'] }}</span>
                                <span class="mt-2 block text-base text-white/90">{{ $atalho['texto'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <x-card padding="p-6 sm:p-8">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-ink">
                        <span class="rounded-lg bg-brand-700/10 p-2 text-brand-700"><x-elemplus-tickets class="size-6" aria-hidden="true" /></span>
                        Vendas de hoje
                    </h2>
                    @if ($comprasHoje > 0)
                        <a href="{{ route('sales.index', ['de' => today()->toDateString(), 'ate' => today()->toDateString()]) }}"
                           class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-brand-700/10 px-4 py-2 text-base font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                            Ver todas ({{ $comprasHoje }}) <x-elemplus-right class="size-5" aria-hidden="true" />
                        </a>
                    @endif
                </div>

                @forelse ($ultimasHoje as $venda)
                    @php
                        $comCusto = $venda->items->whereNotNull('unit_cost');
                        $status = ['paid' => ['Pago', 'bg-green-100 text-green-900'], 'pending' => ['Pendente', 'bg-yellow-100 text-yellow-900']][$venda->status];
                    @endphp
                    <a href="{{ route('sales.show', $venda) }}"
                       class="-mx-3 flex flex-col gap-3 rounded-lg border-t border-edge px-3 py-4 transition-colors first-of-type:border-t-0 hover:bg-surface-muted focus-visible:outline-2 focus-visible:outline-brand-700 sm:flex-row sm:items-center">
                        <span class="w-fit shrink-0 rounded-full bg-brand-700/10 px-3 py-1 text-base font-bold text-brand-700">#{{ $venda->id }}</span>

                        <div class="min-w-0 flex-1">
                            <p class="text-lg font-bold text-ink">
                                {{ $venda->items->map(fn ($item) => $item->quantity.'x '.$item->product->name)->join(' + ') }}
                            </p>
                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-base text-ink-muted">
                                <span>{{ $venda->customer->name }}</span>
                                <span aria-hidden="true">·</span>
                                <span>{{ $venda->created_at->format('H:i') }}</span>
                                <span class="rounded-full px-2 text-sm font-bold {{ $status[1] }}">{{ $status[0] }}</span>
                            </p>
                        </div>

                        <div class="shrink-0 sm:text-right">
                            <p class="text-2xl font-extrabold text-brand-900">{{ $reais($venda->total_amount) }}</p>
                            @if ($comCusto->isNotEmpty())
                                @php $lucro = $comCusto->sum('lucro'); @endphp
                                <p class="text-base font-bold {{ $lucro < 0 ? 'text-red-700' : 'text-green-800' }}">Lucro: {{ $reais($lucro) }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <x-empty-state :acao="route('sales.create')" rotulo="Registrar uma venda">
                        Nenhuma venda hoje ainda.
                    </x-empty-state>
                @endforelse
            </x-card>
        </div>
    </div>
</x-app-layout>
