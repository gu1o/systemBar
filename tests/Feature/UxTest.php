<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

// §A8 — a primeira tela de um usuário novo era um cabeçalho de tabela vazio.
it('orienta o usuário novo em vez de mostrar tabela vazia', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('products.index'))
        ->assertSee('Você ainda não cadastrou nenhum produto.')
        ->assertSee('Cadastrar meu primeiro produto');

    $this->get(route('customers.index'))->assertSee('Você ainda não cadastrou nenhum cliente.');
    $this->get(route('sales.index'))->assertSee('Nenhuma compra registrada ainda.');
});

it('diz o que falta quando a venda é impossível', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('sales.create'))
        ->assertSee('Para registrar uma venda falta um cliente cadastrado e um produto com estoque.')
        ->assertDontSee('Finalizar Venda');

    Customer::factory()->recycle($user)->create();

    $this->get(route('sales.create'))
        ->assertSee('Para registrar uma venda falta um produto com estoque.')
        ->assertSee('Cadastrar um produto');
});

// §A6 — o <select onchange> marcava a venda como paga com um giro da roda do mouse.
it('marca como paga só por botão, e só uma vez', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sale = $user->sales()->create([
        'customer_id' => Customer::factory()->recycle($user)->create()->id,
        'total_amount' => 50.00,
        'status' => 'pending',
    ]);

    $this->get(route('sales.index'))
        ->assertSee('Marcar como pago')
        ->assertDontSee('onchange', false);

    $this->patch(route('sales.updateStatus', $sale), ['status' => 'paid'])
        ->assertSessionHas('success');

    expect($sale->fresh()->status)->toBe('paid');

    // Voltar para pendente por PATCH direto era possível: a tela escondia o caminho,
    // o servidor não.
    $this->patch(route('sales.updateStatus', $sale), ['status' => 'pending'])
        ->assertSessionHasErrors('status');

    expect($sale->fresh()->status)->toBe('paid');
});

// §A7 — session('error') não era renderizado em lugar nenhum do app.
it('mostra mensagem de erro do servidor na tela', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sale = $user->sales()->create([
        'customer_id' => Customer::factory()->recycle($user)->create()->id,
        'total_amount' => 10.00,
        'status' => 'paid',
    ]);

    $this->from(route('sales.index'))
        ->patch(route('sales.updateStatus', $sale), ['status' => 'paid']);

    $this->followingRedirects()
        ->from(route('sales.index'))
        ->patch(route('sales.updateStatus', $sale), ['status' => 'paid'])
        ->assertSee('Esta compra já está marcada como paga.');
});

// §A11 — todas as abas do navegador tinham o mesmo título.
it('dá um título próprio a cada tela', function () {
    $user = User::factory()->create();
    $product = Product::factory()->recycle($user)->create();

    $this->actingAs($user);

    $this->get(route('products.index'))->assertSee('<title>Estoque de Produtos · ', false);
    $this->get(route('products.edit', $product))->assertSee('<title>Editar Produto · ', false);
    $this->get(route('dashboard'))->assertSee('<title>Painel de Controle · ', false);
});
