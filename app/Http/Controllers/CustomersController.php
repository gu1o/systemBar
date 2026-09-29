<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->query('busca'));

        $customers = auth()->user()->customers()
            ->withCount('sales')
            ->when($busca !== '', fn ($query) => $query->where('name', 'like', '%'.$busca.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'busca'));
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
}
