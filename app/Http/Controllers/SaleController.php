<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = auth()->user()->sales()->with(['customer'])->latest()->paginate(10);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $user = auth()->user();

        $products = $user->products()->where('stock_quantity', '>', 0)->get();
        $customers = $user->customers()->orderBy('name')->get();

        return view('sales.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;

        $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // O formulário permite escolher o mesmo produto em duas linhas. Consolidar antes
        // de gravar: um item por produto no comprovante, uma única baixa de estoque.
        $quantities = collect($request->items)
            ->groupBy('product_id')
            ->map(fn ($lines) => (int) $lines->sum('quantity'));

        $products = $request->user()->products()->findMany($quantities->keys())->keyBy('id');

        // Estoque é validado sobre a quantidade já consolidada: linha a linha deixaria
        // passar 3 + 3 num produto com 5. A mensagem nomeia o produto e diz quanto tem —
        // "quantidade inválida" não diz ao dono do comércio o que fazer a seguir.
        $shortages = $quantities
            ->filter(fn ($quantity, $productId) => $quantity > $products[$productId]->stock_quantity)
            ->map(function ($quantity, $productId) use ($products) {
                $available = $products[$productId]->stock_quantity;

                return "Você tem apenas {$available} ".($available === 1 ? 'unidade' : 'unidades')
                    ." de {$products[$productId]->name} em estoque.";
            });

        if ($shortages->isNotEmpty()) {
            throw ValidationException::withMessages(['items' => $shortages->values()->all()]);
        }

        // Tudo numa transação: sem ela, uma falha no meio do loop deixava a venda
        // persistida com R$ 0,00, itens parciais e o estoque já baixado.
        // Total calculado antes de gravar: a venda nasce com o valor certo, sem o
        // update() posterior que deixava R$ 0,00 no banco quando algo falhava.
        $totalAmount = $quantities
            ->map(fn ($quantity, $productId) => $products[$productId]->sale_price * $quantity)
            ->sum();

        DB::transaction(function () use ($request, $quantities, $products, $totalAmount) {
            $sale = $request->user()->sales()->create([
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($quantities as $productId => $quantity) {
                $product = $products[$productId];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->sale_price,
                ]);

                $product->decrement('stock_quantity', $quantity);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Compra registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        abort_unless($sale->user_id === auth()->id(), 403);

        $sale->load(['customer', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        abort_unless($sale->user_id === $request->user()->id, 403);

        $request->validate([
            'status' => 'required|in:pending,paid',
        ]);

        $sale->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status da venda atualizado com sucesso!');
    }
}
