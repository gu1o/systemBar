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
    // Lista, busca e filtro ficam no componente Livewire (app/Livewire/Produtos.php).
    public function index()
    {
        return view('products.index');
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

    // Produtos arquivados, com o caminho de volta (restaurar). Lista e busca no
    // componente Livewire (app/Livewire/ProdutosArquivados.php).
    public function arquivados()
    {
        return view('products.arquivados');
    }

    // Em lote: parte de $user->products(), então id de outro comércio é só ignorado.
    public function arquivarSelecionados(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        $total = $request->user()->products()->whereKey($ids)->delete();

        return redirect()->route('products.index')
            ->with('success', ($total === 1 ? '1 produto arquivado.' : "{$total} produtos arquivados.").' As vendas já registradas continuam completas.');
    }

    public function restaurarSelecionados(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        $total = $request->user()->products()->onlyTrashed()->whereKey($ids)->restore();

        return redirect()->route('products.arquivados')
            ->with('success', ($total === 1 ? '1 produto restaurado.' : "{$total} produtos restaurados.").' Já aparecem na lista e no registro de vendas.');
    }

    public function restaurar(Request $request, Product $product)
    {
        abort_unless($product->user_id === $request->user()->id, 403);

        $product->restore();

        return redirect()->route('products.arquivados')
            ->with('success', "Produto {$product->name} restaurado. Ele voltou para a lista e para o registro de vendas.");
    }
}
