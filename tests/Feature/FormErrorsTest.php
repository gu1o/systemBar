<?php

use App\Models\User;

it('mostra o erro e preserva o que foi digitado no cadastro de cliente', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('customers.create'))
        ->followingRedirects()
        ->post(route('customers.store'), [
            'name' => '',
            'phone' => '(11) 98888-7777',
            'notes' => 'Cliente da esquina',
        ])
        ->assertOk()
        ->assertSee('Corrija o campo abaixo:')
        ->assertSee('Preencha o campo nome.')
        ->assertSee('(11) 98888-7777')      // old() — antes isso era apagado
        ->assertSee('Cliente da esquina');
});

it('mostra os erros no formulário de editar produto', function () {
    $user = User::factory()->create();
    $product = $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 20,
    ]);

    $this->actingAs($user)
        ->from(route('products.edit', $product))
        ->followingRedirects()
        ->put(route('products.update', $product), ['name' => '', 'sale_price' => '', 'stock_quantity' => ''])
        ->assertOk()
        ->assertSee('Corrija os campos abaixo:')
        ->assertSee('Preencha o campo nome.')
        ->assertSee('Preencha o campo preço de venda.');
});
