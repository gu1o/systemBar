<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer'])->latest()->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::where('stock_quantity', '>', 0)->get();
        $customers = Customer::all();
        return view('sales.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $sale = Sale::create([
            'customer_id' => $request->customer_id,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = $product->sale_price * $item['quantity'];

            $sale->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->sale_price,
                'subtotal' => $subtotal,
            ]);

            $product->decrement('stock_quantity', $item['quantity']);
            $totalAmount += $subtotal;
        }

        $sale->update(['total_amount' => $totalAmount]);

        return redirect()->route('sales.index')->with('success', 'Compra registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'status' => 'required|in:pending,paid',
        ]);

        $sale->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status da venda atualizado com sucesso!');
    }
}
