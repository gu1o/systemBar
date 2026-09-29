<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * §F1 — o dashboard era uma closure devolvendo uma grade estática. Sem estes
     * números o dono do comércio abre a lista de Vendas e soma na calculadora.
     *
     * Vendas canceladas ficam fora de todo total: elas existem no histórico
     * justamente para dizer que não valeram (§F4).
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $vendas = fn () => $user->sales()->where('status', '!=', 'cancelled');

        // Lucro usa o custo gravado na venda, não o de hoje do produto. Item sem custo
        // fica fora da conta (contar como zero inflaria o lucro) e a tela diz quantos.
        $itensDoMes = fn () => SaleItem::whereIn('sale_id',
            $vendas()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->select('id'));

        return view('dashboard', [
            'lucroNoMes' => $itensDoMes()->whereNotNull('unit_cost')->sum(DB::raw('(unit_price - unit_cost) * quantity')),
            'itensSemCusto' => $itensDoMes()->whereNull('unit_cost')->count(),
            'vendidoHoje' => $vendas()->whereDate('created_at', today())->sum('total_amount'),
            'vendidoNoMes' => $vendas()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
            'aReceber' => $user->sales()->where('status', 'pending')->sum('total_amount'),
            'contagemAReceber' => $user->sales()->where('status', 'pending')->count(),
            'estoqueBaixo' => $user->products()->whereColumn('stock_quantity', '<=', 'stock_alert')->count(),
        ]);
    }
}
