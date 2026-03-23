<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Converte valor monetário pt-BR (ex.: 1.234,56 ou 25,90) para formato numérico (ponto decimal).
     */
    private function normalizeMoneyForValidation(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $v = trim($value);
        $v = str_replace(['R$', ' ', "\xC2\xA0"], '', $v);

        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }

        return $v;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = auth()->user()->products()->latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Apenas retorna a view com o formulário de criação
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'sale_price' => $this->normalizeMoneyForValidation($request->input('sale_price')) ?? '',
            'cost_price' => $this->normalizeMoneyForValidation($request->input('cost_price')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'stock_alert' => 'nullable|integer|min:0',
        ]);

        unset($validated['user_id']);

        $request->user()->products()->create($validated);

        // Redireciona para a lista de produtos com uma mensagem de sucesso
        return redirect()->route('products.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        abort_unless($product->user_id === auth()->id(), 403);

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        abort_unless($product->user_id === $request->user()->id, 403);

        $request->merge([
            'sale_price' => $this->normalizeMoneyForValidation($request->input('sale_price')) ?? '',
            'cost_price' => $this->normalizeMoneyForValidation($request->input('cost_price')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'stock_alert' => 'nullable|integer|min:0',
        ]);

        $product->update($validated);

        // Redireciona de volta para a lista com mensagem de sucesso
        return redirect()->route('products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
        abort_unless($product->user_id === $request->user()->id, 403);

        $product->delete();

        // Redireciona de volta para a lista com mensagem de sucesso
        return redirect()->route('products.index')
            ->with('success', 'Produto excluído com sucesso!');
    }
}
