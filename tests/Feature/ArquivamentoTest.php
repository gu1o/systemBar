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

// Formulário aberto em outra aba enquanto o produto/cliente é arquivado: o exists
// sem withoutTrashed deixava passar, e o findMany sem o produto dava 500.
it('recusa a venda com produto ou cliente arquivado, sem erro 500', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create(['stock_quantity' => 10]);
    $product->delete();

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('items.0.product_id');

    $product->restore();
    $customer->delete();

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('customer_id');

    expect($user->sales()->count())->toBe(0);
});

it('lista os produtos arquivados e restaura', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $ativo = Product::factory()->recycle($user)->create(['name' => 'Guaraná Lata']);
    $arquivado = Product::factory()->recycle($user)->create(['name' => 'Coca Cola 2L']);
    $arquivado->delete();

    $this->get(route('products.index'))->assertSee(route('products.arquivados'));
    $this->get(route('products.arquivados'))->assertOk()
        ->assertSee('Coca Cola 2L')->assertDontSee('Guaraná Lata');

    $this->patch(route('products.restaurar', $arquivado))->assertRedirect(route('products.arquivados'));

    expect($arquivado->fresh()->trashed())->toBeFalse();
    $this->get(route('products.index'))->assertSee('Coca Cola 2L');
});

it('não deixa ver nem restaurar arquivado de outro comércio', function () {
    $dono = User::factory()->create();
    $produto = Product::factory()->recycle($dono)->create(['name' => 'Produto do Vizinho']);
    $produto->delete();

    $this->actingAs(User::factory()->create());

    $this->get(route('products.arquivados'))->assertOk()->assertDontSee('Produto do Vizinho');
    // 404: o escopo ScopedToUser nem deixa o produto do outro comércio ser encontrado.
    $this->patch(route('products.restaurar', $produto))->assertNotFound();
    expect($produto->fresh()->trashed())->toBeTrue();
});

it('arquiva e restaura vários produtos de uma vez, só os do próprio comércio', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    [$a, $b, $c] = Product::factory()->recycle($user)->count(3)->create();
    $doVizinho = Product::factory()->recycle(User::factory()->create())->create();

    $this->delete(route('products.arquivarSelecionados'), ['ids' => [$a->id, $b->id, $doVizinho->id]])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success', fn ($msg) => str_starts_with($msg, '2 produtos arquivados'));

    expect($a->fresh()->trashed())->toBeTrue()
        ->and($b->fresh()->trashed())->toBeTrue()
        ->and($c->fresh()->trashed())->toBeFalse()
        ->and(Product::withoutGlobalScopes()->find($doVizinho->id)->trashed())->toBeFalse();

    $this->patch(route('products.restaurarSelecionados'), ['ids' => [$a->id, $b->id]])
        ->assertRedirect(route('products.arquivados'));

    expect($a->fresh()->trashed())->toBeFalse()->and($b->fresh()->trashed())->toBeFalse();

    $this->delete(route('products.arquivarSelecionados'), ['ids' => []])->assertSessionHasErrors('ids');
});

it('busca entre os arquivados sem recarregar', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->recycle($user)->create(['name' => 'Cerveja Lata'])->delete();
    Product::factory()->recycle($user)->create(['name' => 'Detergente'])->delete();
    Product::factory()->recycle($user)->create(['name' => 'Cerveja Ativa']);

    \Livewire\Livewire::test(\App\Livewire\ProdutosArquivados::class)
        ->assertSee('Cerveja Lata')->assertSee('Detergente')->assertDontSee('Cerveja Ativa')
        ->set('busca', 'Cerveja')
        ->assertSee('Cerveja Lata')->assertDontSee('Detergente')->assertDontSee('Cerveja Ativa');
});
