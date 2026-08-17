<?php

use App\Models\User;

it('abre todas as telas principais sem erro', function () {
    $user = User::factory()->create();
    $customer = $user->customers()->create(['name' => 'Maria Souza']);
    $product = $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 3,
    ]);

    $this->actingAs($user);

    $telas = [
        route('dashboard'),
        route('products.index'),
        route('products.create'),
        route('products.edit', $product),
        route('customers.index'),
        route('customers.create'),
        route('customers.edit', $customer),
        route('sales.index'),
        route('sales.create'),
        route('profile.edit'),
    ];

    foreach ($telas as $tela) {
        $this->get($tela)->assertOk();
    }
});

it('mostra as ações das listas como texto, não só ícone', function () {
    $user = User::factory()->create();
    $user->customers()->create(['name' => 'Maria Souza']);
    $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 3,
    ]);

    $this->actingAs($user);

    $this->get(route('products.index'))->assertSee('Editar')->assertSee('Excluir');
    $this->get(route('customers.index'))->assertSee('Editar')->assertSee('Excluir');
});
