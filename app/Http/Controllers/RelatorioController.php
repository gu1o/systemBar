<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Relatório de faturamento por período: quanto vendeu, quanto lucrou e quanto
 * falta receber, dia a dia. Padrão é o mês corrente até hoje, que é a pergunta
 * de sempre ("como está o mês?").
 *
 * Canceladas ficam fora, como em todo total (§F4).
 */
class RelatorioController extends Controller
{
    public function __invoke(Request $request)
    {
        $de = $this->data($request->query('de')) ?? now()->startOfMonth()->toDateString();
        $ate = $this->data($request->query('ate')) ?? now()->toDateString();

        // "De 30 até 1" é engano de digitação, não pedido de período vazio.
        if ($de > $ate) {
            [$de, $ate] = [$ate, $de];
        }

        // ponytail: agrega em PHP, não em SQL — cabe no volume de um pequeno comércio
        // (centenas de vendas/mês). Se o relatório de um ano ficar lento, trocar por GROUP BY.
        $vendas = $request->user()->sales()
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

        return view('relatorio', [
            'de' => $de,
            'ate' => $ate,
            'dias' => $dias,
            'compras' => $vendas->count(),
            'vendido' => $vendas->sum('total_amount'),
            'lucro' => $itens->sum('lucro'),
            'aReceber' => $vendas->where('status', 'pending')->sum('total_amount'),
            'itensSemCusto' => $itens->whereNull('unit_cost')->count(),
        ]);
    }
}
