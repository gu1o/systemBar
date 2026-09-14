<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

// §B4 — o cascade da FK de sale_items apagava os itens de todas as vendas passadas
// do produto, enquanto sales.total_amount continuava com o valor antigo.
it('mantém as vendas antigas completas depois de excluir o produto', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create([
        'name' => 'Coca Cola 2L', 'sale_price' => 10.00, 'stock_quantity' => 10,
    ]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ]);

    $sale = $user->sales()->sole();

    $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));

    expect($sale->fresh()->items)->toHaveCount(1)
        ->and((float) $sale->fresh()->total_amount)->toBe(20.00);

    // Some da lista, continua no comprovante.
    $this->get(route('products.index'))->assertDontSee('Coca Cola 2L');
    $this->get(route('sales.show', $sale))->assertOk()->assertSee('Coca Cola 2L');
});

// §B3 — excluir um cliente com vendas derrubava a lista de Vendas inteira com
// "Attempt to read property name on null", não só a venda dele.
it('mantém a lista de vendas abrindo depois de excluir o cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create(['name' => 'João da Silva']);
    $product = Product::factory()->recycle($user)->create(['stock_quantity' => 10]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]);

    $sale = $user->sales()->sole();

    $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));

    $this->get(route('customers.index'))->assertOk()->assertDontSee('João da Silva');
    $this->get(route('sales.index'))->assertOk()->assertSee('João da Silva');
    $this->get(route('sales.show', $sale))->assertOk()->assertSee('João da Silva');
});

// A venda antiga pode ter customer_id nulo de verdade (coluna nullable, FK set null
// de antes do arquivamento) — a tela não pode quebrar por causa disso.
it('mostra "Cliente removido" quando a venda está sem cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sale = $user->sales()->create([
        'customer_id' => null, 'total_amount' => 10.00, 'status' => 'pending',
    ]);

    $this->get(route('sales.index'))->assertOk()->assertSee('Cliente removido');
    $this->get(route('sales.show', $sale))->assertOk()->assertSee('Cliente removido');
});
