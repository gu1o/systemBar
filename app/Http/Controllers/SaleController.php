<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $totalAmount = 0;
        $sale = $request->user()->sales()->create([
            'customer_id' => $request->customer_id,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        foreach ($quantities as $productId => $quantity) {
            $product = $request->user()->products()->findOrFail($productId);

            $sale->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->sale_price,
            ]);

            $product->decrement('stock_quantity', $quantity);
            $totalAmount += $product->sale_price * $quantity;
        }

        $sale->update(['total_amount' => $totalAmount]);

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
