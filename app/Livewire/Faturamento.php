<?php

namespace App\Livewire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Relatório de faturamento por período: quanto vendeu, quanto lucrou e quanto
 * falta receber, dia a dia. Padrão é hoje ("como foi o dia?"). O período troca
 * sem recarregar (mesmo filtro de Vendas) e fica na URL: favorito e F5 abrem igual.
 *
 * Canceladas ficam fora, como em todo total (§F4).
 */
class Faturamento extends Component
{
    #[Url(except: '')]
    public string $periodo = '';

    #[Url(except: '')]
    public string $de = '';

    #[Url(except: '')]
    public string $ate = '';

    // Personalizado começa no intervalo que já está na tela: abrir as datas não
    // troca o relatório por baixo da pessoa.
    public function updatingPeriodo(string $novo): void
    {
        if ($novo === 'personalizado') {
            [, $this->de, $this->ate] = $this->resolverPeriodo();
        }
    }

    public function aplicarPeriodo(string $de, string $ate): void
    {
        [$this->periodo, $this->de, $this->ate] = ['personalizado', $de, $ate];
    }

    public function limparPeriodo(): void
    {
        [$this->periodo, $this->de, $this->ate] = ['hoje', '', ''];
    }

    private function resolverPeriodo(): array
    {
        return Controller::periodo(new Request(array_filter([
            'periodo' => $this->periodo, 'de' => $this->de, 'ate' => $this->ate,
        ])));
    }

    public function render()
    {
        // Valor inválido na URL vira o resolvido, para o select mostrar o que está valendo.
        [$periodo, $de, $ate] = $this->resolverPeriodo();
        $this->periodo = $periodo;
        [$this->de, $this->ate] = $periodo === 'personalizado' ? [$de, $ate] : ['', ''];

        // ponytail: agrega em PHP, não em SQL — cabe no volume de um pequeno comércio
        // (centenas de vendas/mês). Se o relatório de um ano ficar lento, trocar por GROUP BY.
        $vendas = auth()->user()->sales()
            ->with('items')
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $de)
            ->whereDate('created_at', '<=', $ate)
            ->oldest()
            ->get();

        $itens = $vendas->flatMap->items;

        $dias = $vendas
            ->groupBy(fn ($venda) => $venda->created_at->toDateString())
            ->map(fn ($doDia) => [
                'compras' => $doDia->count(),
                'vendido' => $doDia->sum('total_amount'),
                'lucro' => $doDia->flatMap->items->sum('lucro'),
            ]);

        // inicio/fim, não de/ate: as propriedades públicas de mesmo nome sobrescreveriam.
        return view('livewire.faturamento', [
            'inicio' => $de,
            'fim' => $ate,
            'dias' => $dias,
            'compras' => $vendas->count(),
            'vendido' => $vendas->sum('total_amount'),
            'lucro' => $itens->sum('lucro'),
            'aReceber' => $vendas->where('status', 'pending')->sum('total_amount'),
            'itensSemCusto' => $itens->whereNull('unit_cost')->count(),
        ]);
    }
}
