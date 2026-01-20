<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca todos os produtos do banco de dados
        $products = Product::latest()->paginate(10); // Pega os últimos adicionados e pagina

        // Retorna a view 'products.index' e passa a variável 'products' para ela
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
        // Validação dos dados do formulário
        $request->validate([
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        // Cria o produto no banco de dados
        Product::create($request->all());

        // Redireciona para a lista de produtos com uma mensagem de sucesso
        return redirect()->route('products.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Retorna a view de edição, passando o produto que queremos editar
        // O Laravel automaticamente encontra o produto pelo ID na URL ({produto})
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Validação dos dados
        $request->validate([
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        // Atualiza o produto no banco de dados
        $product->update($request->all());

        // Redireciona de volta para a lista com mensagem de sucesso
        return redirect()->route('products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Deleta o produto do banco de dados
        $product->delete();

        // Redireciona de volta para a lista com mensagem de sucesso
        return redirect()->route('products.index')
            ->with('success', 'Produto excluído com sucesso!');
    }
}
