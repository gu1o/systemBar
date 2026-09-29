<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $busca = trim((string) $request->query('busca'));

        $products = auth()->user()->products()
            ->when($busca !== '', fn ($query) => $query->where('name', 'like', '%'.$busca.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products', 'busca'));
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
    public function store(ProductRequest $request)
    {
        $request->user()->products()->create($request->validated());

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
    public function update(ProductRequest $request, Product $product)
    {
        abort_unless($product->user_id === $request->user()->id, 403);

        $product->update($request->validated());

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
            ->with('success', 'Produto arquivado. As vendas já registradas continuam completas.');
    }
}
