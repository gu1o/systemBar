@php
    $reais = fn ($valor) => 'R$ '.number_format($valor, 2, ',', '.');
    $opcoes = \App\Http\Controllers\Controller::PERIODOS;

    $br = fn ($dia) => \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y');
    $datas = $inicio === $fim ? $br($inicio) : 'de '.$br($inicio).' até '.$br($fim);
    $resumo = $periodo === 'personalizado' ? $datas : $opcoes[$periodo].' — '.$datas;
@endphp

{{-- Estado do bloco de datas no Alpine: abre e fecha no clique, sem esperar o servidor. --}}
<div x-data="{ personalizado: @js($periodo === 'personalizado') }" class="space-y-6">
    <x-card>
        <div class="nao-imprimir flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:w-64">
                <label for="periodo" class="mb-1 block text-lg font-bold text-ink">Período</label>
                <x-select id="periodo" wire:model.live="periodo" @change="personalizado = $event.target.value === 'personalizado'" class="min-h-12 w-full text-lg">
                    @foreach ($opcoes as $valor => $nome)
                        <option value="{{ $valor }}">{{ $nome }}</option>
                    @endforeach
                </x-select>
            </div>

            @if ($periodo !== array_key_first($opcoes))
                <button type="button" wire:click="limparPeriodo" @click="personalizado = false"
                        class="inline-flex min-h-12 animate-entra cursor-pointer items-center justify-center rounded-lg border-2 border-gray-400 px-6 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    Limpar filtro
                </button>
            @endif
        </div>

        {{-- Mesmo bloco de Vendas: sempre no HTML, aberto pelo Alpine (x-collapse);
             wire:ignore.self porque a resposta do Livewire desfazia o x-show. --}}
        <form wire:ignore.self x-show="personalizado" x-collapse.duration.300ms @style(["display: none" => $periodo !== 'personalizado'])
              wire:submit="aplicarPeriodo($event.target.de.value, $event.target.ate.value)"
              aria-label="Escolher datas personalizadas"
              class="nao-imprimir">
            <div class="mt-4 flex animate-entra flex-col gap-4 rounded-lg bg-gray-50 p-4 sm:flex-row sm:items-end">
                {{-- wire:key com as datas: período trocado recria o calendário com os valores novos. --}}
                <div wire:key="datas-{{ $de }}-{{ $ate }}">
                    <x-date-range :de="$de" :ate="$ate" />
                </div>

                <button type="submit"
                        class="inline-flex min-h-12 cursor-pointer items-center justify-center rounded-lg bg-brand-700 px-5 text-lg font-bold text-white transition-colors hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    Aplicar datas
                </button>
            </div>
        </form>

        {{-- Por extenso: confirma o que está na tela sem ler os campos. Imprime
             (o relatório impresso precisa dizer de quando é). --}}
        <p class="mt-4 text-lg text-ink-muted print:mt-0" aria-live="polite">
            <span wire:loading.remove>Período filtrado: <strong class="text-xl text-ink">{{ ucfirst($resumo) }}</strong></span>
            <span wire:loading>Carregando...</span>
        </p>
    </x-card>

    <div wire:loading.class="opacity-50" class="space-y-6 transition-opacity">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-card padding="p-6">
                <p class="text-lg font-bold text-ink-muted">Vendido</p>
                <p class="mt-2 text-4xl font-extrabold text-brand-900">{{ $reais($vendido) }}</p>
                <p class="mt-1 text-base text-ink-muted">{{ $compras }} {{ $compras === 1 ? 'compra' : 'compras' }}</p>
            </x-card>

            <x-card padding="p-6">
                <p class="text-lg font-bold text-ink-muted">Lucro</p>
                <p class="mt-2 text-4xl font-extrabold {{ $lucro < 0 ? 'text-red-700' : 'text-green-800' }}">{{ $reais($lucro) }}</p>
                @if ($itensSemCusto > 0)
                    <p class="mt-1 text-base text-ink-muted">
                        {{ $itensSemCusto }} {{ $itensSemCusto === 1 ? 'produto vendido sem preço de custo ficou' : 'produtos vendidos sem preço de custo ficaram' }} fora da conta.
                    </p>
                @endif
            </x-card>

            <x-card padding="p-6">
                <p class="text-lg font-bold text-ink-muted">Ainda a receber</p>
                <p class="mt-2 text-4xl font-extrabold text-brand-900">{{ $reais($aReceber) }}</p>
                <p class="mt-1 text-base text-ink-muted">das compras deste período</p>
            </x-card>

            <x-card padding="p-6">
                <p class="text-lg font-bold text-ink-muted">Média por compra</p>
                <p class="mt-2 text-4xl font-extrabold text-brand-900">{{ $reais($compras ? $vendido / $compras : 0) }}</p>
            </x-card>
        </div>

        <x-card>
            <h3 class="mb-4 text-2xl font-bold text-ink">Dia a dia</h3>

            <table class="tabela-cartoes min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Dia</th>
                        <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Compras</th>
                        <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Vendido</th>
                        <th class="px-6 py-3 text-left text-base font-semibold text-gray-700">Lucro</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($dias as $dia => $totais)
                        <tr wire:key="dia-{{ $dia }}">
                            <td data-rotulo="Dia" class="px-6 py-4 text-lg font-medium text-gray-900">
                                {{ \Illuminate\Support\Carbon::parse($dia)->translatedFormat('d/m/Y (D)') }}
                            </td>
                            <td data-rotulo="Compras" class="px-6 py-4 text-lg text-gray-900">{{ $totais['compras'] }}</td>
                            <td data-rotulo="Vendido" class="whitespace-nowrap px-6 py-4 text-lg font-bold text-gray-900">{{ $reais($totais['vendido']) }}</td>
                            <td data-rotulo="Lucro" class="whitespace-nowrap px-6 py-4 text-lg font-bold {{ $totais['lucro'] < 0 ? 'text-red-700' : 'text-green-800' }}">{{ $reais($totais['lucro']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state :acao="route('sales.create')" rotulo="Registrar uma venda">
                                    Nenhuma venda neste período.
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Mesmo botão do comprovante de Vendas. --}}
            <div class="nao-imprimir mt-8 flex justify-center">
                <button type="button" onclick="window.print()"
                    class="bg-brand-700 hover:bg-brand-600 text-white font-bold py-3 px-4 md:px-8 rounded-lg shadow-lg transition-all text-lg md:text-xl flex w-full items-center justify-center whitespace-nowrap md:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Relatório
                </button>
            </div>
        </x-card>
    </div>
</div>
