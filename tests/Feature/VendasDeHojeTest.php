<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

it('lista as vendas de hoje no painel, sem a cancelada', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create(['name' => 'Maria']);
    $skol = Product::factory()->recycle($user)->create(['name' => 'Skol Lata', 'sale_price' => 5.00, 'cost_price' => 3.00, 'stock_quantity' => 10]);
    $torresmo = Product::factory()->recycle($user)->create(['name' => 'Torresmo', 'sale_price' => 12.00, 'cost_price' => null, 'stock_quantity' => 10]);

    $this->post(route('sales.store'), ['customer_id' => $customer->id, 'items' => [
        ['product_id' => $skol->id, 'quantity' => 2],
        ['product_id' => $torresmo->id, 'quantity' => 1],
    ]]);
    $this->post(route('sales.store'), ['customer_id' => $customer->id, 'items' => [
        ['product_id' => $torresmo->id, 'quantity' => 3],
    ]]);
    $user->sales()->latest('id')->first()->update(['status' => 'cancelled']);

    // Lucro só do item com custo: 2 × (5 − 3) = 4.
    $this->get(route('dashboard'))
        ->assertSee('2x Skol Lata + 1x Torresmo')
        ->assertSee('R$ 22,00')
        ->assertSee('Lucro: R$ 4,00')
        ->assertDontSee('3x Torresmo');
});

it('mostra aviso quando ainda não há venda hoje', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('dashboard'))->assertSee('Nenhuma venda hoje ainda.');
});
