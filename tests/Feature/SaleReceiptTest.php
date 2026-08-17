<?php

use App\Models\User;

it('mostra o valor de cada linha no comprovante e a soma bate com o total geral', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = $user->customers()->create(['name' => 'Maria Souza', 'phone' => '11999990000']);

    $refrigerante = $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 20,
    ]);
    $salgadinho = $user->products()->create([
        'name' => 'Salgadinho', 'sale_price' => 7.25, 'stock_quantity' => 20,
    ]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [
            ['product_id' => $refrigerante->id, 'quantity' => 3],  // 31,50
            ['product_id' => $salgadinho->id, 'quantity' => 2],    // 14,50
        ],
    ])->assertRedirect(route('sales.index'));

    $sale = $user->sales()->latest()->firstOrFail();

    expect((float) $sale->total_amount)->toBe(46.00);

    $this->get(route('sales.show', $sale))
        ->assertSee('R$ 31,50')   // linha do refrigerante — hoje sai R$ 0,00
        ->assertSee('R$ 14,50')   // linha do salgadinho
        ->assertSee('R$ 46,00');  // total geral
});

it('junta o mesmo produto escolhido em duas linhas num único item', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = $user->customers()->create(['name' => 'João da Silva']);
    $product = $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 20,
    ]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 3],
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ])->assertRedirect(route('sales.index'));

    $sale = $user->sales()->latest()->firstOrFail();

    expect($sale->items)->toHaveCount(1)
        ->and($sale->items->first()->quantity)->toBe(5)
        ->and((float) $sale->total_amount)->toBe(52.50)
        ->and($product->fresh()->stock_quantity)->toBe(15);
});
