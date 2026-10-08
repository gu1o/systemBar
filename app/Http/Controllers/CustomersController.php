<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index()
    {
        return view('customers.index');
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request)
    {
        $request->user()->customers()->create($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Customer $customer)
    {
        abort_unless($customer->user_id === auth()->id(), 403);

        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        abort_unless($customer->user_id === $request->user()->id, 403);

        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Request $request, Customer $customer)
    {
        abort_unless($customer->user_id === $request->user()->id, 403);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Cliente arquivado. As compras dele continuam no histórico.');
    }

    public function arquivados()
    {
        return view('customers.arquivados');
    }

    public function arquivarSelecionados(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        $total = $request->user()->customers()->whereKey($ids)->delete();

        return redirect()->route('customers.index')
            ->with('success', ($total === 1 ? '1 cliente arquivado.' : "{$total} clientes arquivados.").' As compras continuam no histórico.');
    }

    public function restaurarSelecionados(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        $total = $request->user()->customers()->onlyTrashed()->whereKey($ids)->restore();

        return redirect()->route('customers.arquivados')
            ->with('success', ($total === 1 ? '1 cliente restaurado.' : "{$total} clientes restaurados.").' Já aparecem na lista e no registro de vendas.');
    }

    public function restaurar(Request $request, Customer $customer)
    {
        abort_unless($customer->user_id === $request->user()->id, 403);

        $customer->restore();

        return redirect()->route('customers.arquivados')
            ->with('success', "Cliente {$customer->name} restaurado. Ele voltou para a lista e para o registro de vendas.");
    }
}
