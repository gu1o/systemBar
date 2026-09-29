<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

/**
 * Isolamento multi-tenant: cada usuário cadastrado é um comércio separado, e este
 * era o objetivo inteiro do commit ad4f1a8 — sem nenhum teste até aqui (§T1).
 */
it('não mostra a um comércio os produtos e clientes de outro', function () {
    $dono = User::factory()->create();
    $vizinho = User::factory()->create();

    Product::factory()->recycle($vizinho)->create(['name' => 'Produto do Vizinho']);
    Customer::factory()->recycle($vizinho)->create(['name' => 'Cliente do Vizinho']);

    $this->actingAs($dono);

    $this->get(route('products.index'))->assertDontSee('Produto do Vizinho');
    $this->get(route('customers.index'))->assertDontSee('Cliente do Vizinho');

    expect($dono->products()->count())->toBe(0)
        ->and(Product::count())->toBe(0);   // o escopo global vale na consulta crua
});

it('não deixa abrir, editar nem excluir registro de outro comércio', function () {
    $dono = User::factory()->create();
    $vizinho = User::factory()->create();

    $produtoAlheio = Product::factory()->recycle($vizinho)->create();
    $clienteAlheio = Customer::factory()->recycle($vizinho)->create();
    $vendaAlheia = Sale::factory()->recycle($vizinho)->create(['customer_id' => $clienteAlheio->id]);

    $this->actingAs($dono);

    // O escopo global esconde a linha, então o route model binding nem resolve: 404.
    $this->get(route('products.edit', $produtoAlheio))->assertNotFound();
    $this->get(route('customers.edit', $clienteAlheio))->assertNotFound();
    $this->get(route('sales.show', $vendaAlheia))->assertNotFound();

    $this->delete(route('products.destroy', $produtoAlheio))->assertNotFound();
    $this->patch(route('sales.cancel', $vendaAlheia))->assertNotFound();

    expect($produtoAlheio->fresh()->exists)->toBeTrue()
        ->and($vendaAlheia->fresh()->status)->toBe('pending');
});

it('não deixa vender o produto nem faturar no nome do cliente de outro comércio', function () {
    $dono = User::factory()->create();
    $vizinho = User::factory()->create();

    $produtoAlheio = Product::factory()->recycle($vizinho)->create(['stock_quantity' => 10]);
    $clienteAlheio = Customer::factory()->recycle($vizinho)->create();
    $meuCliente = Customer::factory()->recycle($dono)->create();
    $meuProduto = Product::factory()->recycle($dono)->create(['stock_quantity' => 10]);

    $this->actingAs($dono);

    $this->post(route('sales.store'), [
        'customer_id' => $clienteAlheio->id,
        'items' => [['product_id' => $meuProduto->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('customer_id');

    $this->post(route('sales.store'), [
        'customer_id' => $meuCliente->id,
        'items' => [['product_id' => $produtoAlheio->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('items.0.product_id');

    expect($dono->sales()->count())->toBe(0)
        ->and($produtoAlheio->fresh()->stock_quantity)->toBe(10);
});
