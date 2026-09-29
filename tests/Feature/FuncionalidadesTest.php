<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

// §F4 — venda registrada por engano não tinha saída nenhuma: sem editar, sem
// excluir, sem cancelar.
it('cancela a compra devolvendo os produtos ao estoque', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create(['sale_price' => 10.00, 'stock_quantity' => 10]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 4]],
    ]);

    $sale = $user->sales()->sole();
    expect($product->fresh()->stock_quantity)->toBe(6);

    $this->patch(route('sales.cancel', $sale))->assertSessionHas('success');

    expect($sale->fresh()->status)->toBe('cancelled')
        ->and($product->fresh()->stock_quantity)->toBe(10);

    // Cancelar de novo não pode devolver estoque duas vezes.
    $this->patch(route('sales.cancel', $sale))->assertSessionHas('error');
    expect($product->fresh()->stock_quantity)->toBe(10);

    // A compra continua no histórico, marcada.
    $this->get(route('sales.index'))->assertSee('Cancelada');
});

it('não deixa marcar como paga uma compra cancelada', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sale = $user->sales()->create([
        'customer_id' => Customer::factory()->recycle($user)->create()->id,
        'total_amount' => 10.00,
        'status' => 'cancelled',
    ]);

    $this->patch(route('sales.updateStatus', $sale), ['status' => 'paid'])->assertSessionHas('error');

    expect($sale->fresh()->status)->toBe('cancelled');
});

// §F1 — o dashboard era uma grade estática; o usuário somava na calculadora.
it('mostra no painel o vendido, o a receber e o estoque baixo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    Product::factory()->recycle($user)->create(['stock_quantity' => 2, 'stock_alert' => 5]);
    Product::factory()->recycle($user)->create(['stock_quantity' => 50, 'stock_alert' => 5]);

    $user->sales()->create(['customer_id' => $customer->id, 'total_amount' => 30.00, 'status' => 'paid']);
    $user->sales()->create(['customer_id' => $customer->id, 'total_amount' => 20.00, 'status' => 'pending']);
    // Cancelada fica fora de todo total.
    $user->sales()->create(['customer_id' => $customer->id, 'total_amount' => 99.00, 'status' => 'cancelled']);

    $this->get(route('dashboard'))
        ->assertSee('R$ 50,00')           // vendido hoje: 30 + 20, sem a cancelada
        ->assertSee('1 compra pendente')  // a receber
        ->assertSee('produto para repor')
        ->assertDontSee('R$ 149,00');
});

// §F2 — stock_alert existia, era validado e não tinha campo: valia 5 para sempre.
it('usa o limiar de estoque escolhido pelo dono, não o 5 fixo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->recycle($user)->create([
        'name' => 'Cerveja Lata', 'stock_quantity' => 8, 'stock_alert' => 12,
    ]);

    $this->get(route('products.index'))->assertSee('Baixo Estoque');
    $this->get(route('products.create'))->assertSee('Avisar quando o estoque chegar em');

    $this->patch(route('products.update', $user->products()->sole()), [
        'name' => 'Cerveja Lata', 'sale_price' => '5,00', 'stock_quantity' => 8, 'stock_alert' => 3,
    ])->assertRedirect(route('products.index'));

    expect($user->products()->sole()->stock_alert)->toBe(3);
    $this->get(route('products.index'))->assertDontSee('Baixo Estoque');
});

// §F3 — sem busca, quem tem 60 produtos navega 6 páginas para achar um.
it('busca produto e cliente pelo nome', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->recycle($user)->create(['name' => 'Coca Cola 2L']);
    Product::factory()->recycle($user)->create(['name' => 'Detergente']);
    Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    Customer::factory()->recycle($user)->create(['name' => 'Joao da Silva']);

    $this->get(route('products.index', ['busca' => 'coca']))
        ->assertSee('Coca Cola 2L')
        ->assertDontSee('Detergente');

    $this->get(route('customers.index', ['busca' => 'maria']))
        ->assertSee('Maria Souza')
        ->assertDontSee('Joao da Silva');

    // Busca sem resultado explica o que houve, sem fingir que o usuário é novo.
    $this->get(route('products.index', ['busca' => 'inexistente']))
        ->assertSee('Nenhum produto com "inexistente" no nome.', false)
        ->assertDontSee('Você ainda não cadastrou');
});

it('filtra as compras por situação', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $joao = Customer::factory()->recycle($user)->create(['name' => 'Joao da Silva']);

    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $user->sales()->create(['customer_id' => $joao->id, 'total_amount' => 10, 'status' => 'paid']);

    $this->get(route('sales.index', ['situacao' => 'pending']))
        ->assertSee('Maria Souza')
        ->assertDontSee('Joao da Silva');
});

it('filtra as compras por período', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $joao = Customer::factory()->recycle($user)->create(['name' => 'Joao da Silva']);

    $this->travelTo('2026-09-01 20:00');
    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $this->travelTo('2026-09-10 09:00');
    $user->sales()->create(['customer_id' => $joao->id, 'total_amount' => 10, 'status' => 'pending']);
    $this->travelBack();

    // Os dois limites incluem o dia inteiro.
    $this->get(route('sales.index', ['de' => '2026-09-01', 'ate' => '2026-09-01']))
        ->assertSee('Maria Souza')
        ->assertDontSee('Joao da Silva');

    $this->get(route('sales.index', ['de' => '2026-09-05']))
        ->assertSee('Joao da Silva')
        ->assertDontSee('Maria Souza');

    // Data impossível é ignorada, não quebra a tela.
    $this->get(route('sales.index', ['de' => '2026-02-31']))->assertOk()->assertSee('Maria Souza');
});

// §B3 — o aviso de arquivar diz quantas compras o cliente tem antes de a pessoa confirmar.
it('avisa quantas compras o cliente tem antes de arquivar', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'paid']);

    $this->get(route('customers.index'))->assertSee('Este cliente tem 2 compras registradas.');
});
