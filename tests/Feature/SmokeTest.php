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

    $this->get(route('products.index'))->assertSee('Editar')->assertSee('Arquivar');
    $this->get(route('customers.index'))->assertSee('Editar')->assertSee('Arquivar');
});

// §B6 — a máscara de moeda morava só em products/create, então cada tecla digitada
// num preço na tela de edição lançava ReferenceError.
it('carrega a máscara de moeda nas duas telas de produto', function () {
    $user = User::factory()->create();
    $product = $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 3,
    ]);

    $this->actingAs($user);

    foreach ([route('products.create'), route('products.edit', $product)] as $tela) {
        $this->get($tela)
            ->assertSee('const brlCurrencyMask', false)
            ->assertSee('oninput="brlCurrencyMask(event)"', false);
    }
});

// §B9 — Route::resource registrava rotas sem método no controller: acessar uma delas
// dava 500 (BadMethodCallException). Agora responde 405 — a URI segue registrada para
// PUT/DELETE, então não dá pra chegar em 404 sem renomear a rota. O que importa é que
// deixou de ser erro de servidor.
it('não dá erro de servidor nas rotas que o controller não implementa', function () {
    $user = User::factory()->create();
    $product = $user->products()->create([
        'name' => 'Refrigerante 2L', 'sale_price' => 10.50, 'stock_quantity' => 3,
    ]);
    $customer = $user->customers()->create(['name' => 'Maria Souza']);

    $this->actingAs($user);

    $this->get("/products/{$product->id}")->assertMethodNotAllowed();
    $this->get("/customers/{$customer->id}")->assertMethodNotAllowed();
    $this->get("/sales/{$product->id}/edit")->assertNotFound();
});

// §A2 — a paginação era a view padrão do Laravel: alvos pequenos e, sem o lang
// publicado, "Previous/Next" em inglês.
it('pagina em português e diz em que página o usuário está', function () {
    $user = User::factory()->create();
    \App\Models\Product::factory()->recycle($user)->count(12)->create();

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertSee('Página 1 de 2')
        ->assertSee('Próxima', false)
        ->assertDontSee('Next');
});
