<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

// Margem de lucro — o cost_price era coletado e nunca usado.
it('mostra o lucro do mês com o custo gravado na venda', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $coca = Product::factory()->recycle($user)->create(['sale_price' => 10.00, 'cost_price' => 6.00, 'stock_quantity' => 10]);
    $semCusto = Product::factory()->recycle($user)->create(['sale_price' => 5.00, 'cost_price' => null, 'stock_quantity' => 10]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [
            ['product_id' => $coca->id, 'quantity' => 3],
            ['product_id' => $semCusto->id, 'quantity' => 2],
        ],
    ]);

    // Custo novo não reescreve o lucro da venda passada.
    $coca->update(['cost_price' => 9.00]);

    // 3 × (10 − 6) = 12; o item sem custo fica fora, não entra como lucro de R$ 10.
    $this->get(route('dashboard'))
        ->assertSee('Lucro: R$ 12,00')
        ->assertSee('1 produto vendido sem preço de custo ficou fora da conta.');
});

it('não conta o lucro de compra cancelada', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create(['sale_price' => 10.00, 'cost_price' => 6.00, 'stock_quantity' => 10]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ]);
    $this->patch(route('sales.cancel', $user->sales()->sole()));

    $this->get(route('dashboard'))->assertSee('Lucro: R$ 0,00');
});

it('mostra o lucro por unidade na lista de produtos', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->recycle($user)->create(['name' => 'Coca', 'sale_price' => 10.00, 'cost_price' => 7.50]);
    Product::factory()->recycle($user)->create(['name' => 'Pão', 'sale_price' => 1.00, 'cost_price' => null]);

    $this->get(route('products.index'))
        ->assertSee('R$ 2,50')
        ->assertSee('(25%)')
        ->assertSee('Sem preço de custo');
});

// Achado no teste de ponta a ponta: a edição mostrava 1234.56 (formato americano)
// enquanto o cadastro mostra 1.234,56 — e salvar sem mexer tem que manter o valor.
it('mostra o preço no formato brasileiro na edição e salva sem alterar', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->recycle($user)->create(['sale_price' => 1234.56, 'cost_price' => null]);

    $this->get(route('products.edit', $product))->assertSee('value="1.234,56"', false);

    $this->put(route('products.update', $product), [
        'name' => $product->name, 'sale_price' => '1.234,56', 'cost_price' => '',
        'stock_quantity' => $product->stock_quantity,
    ])->assertSessionHasNoErrors();

    expect((float) $product->fresh()->sale_price)->toBe(1234.56)
        ->and($product->fresh()->cost_price)->toBeNull();
});
