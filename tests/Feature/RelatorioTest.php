<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

function venderEm(string $quando, User $user, Customer $customer, Product $product, int $quantidade): void
{
    test()->travelTo($quando);
    test()->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => $quantidade]],
    ]);
    test()->travelBack();
}

it('soma vendido, lucro e a receber do período, dia a dia', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create(['sale_price' => 10.00, 'cost_price' => 6.00, 'stock_quantity' => 100]);

    venderEm('2026-09-02 10:00', $user, $customer, $product, 1);
    venderEm('2026-09-02 18:00', $user, $customer, $product, 2);
    venderEm('2026-09-05 09:00', $user, $customer, $product, 4);
    venderEm('2026-08-31 23:00', $user, $customer, $product, 50); // fora do período

    // Uma paga, uma cancelada: a paga sai do "a receber", a cancelada sai de tudo.
    $user->sales()->whereDate('created_at', '2026-09-05')->sole()->update(['status' => 'paid']);
    venderEm('2026-09-05 12:00', $user, $customer, $product, 7);
    $this->patch(route('sales.cancel', $user->sales()->latest('id')->first()));

    $this->get(route('relatorio', ['de' => '2026-09-01', 'ate' => '2026-09-30']))
        ->assertOk()
        ->assertSee('De 01/09/2026 até 30/09/2026')
        ->assertSee('R$ 70,00')   // vendido: 10 + 20 + 40
        ->assertSee('R$ 28,00')   // lucro: 7 × 4
        ->assertSee('R$ 30,00')   // a receber: as duas do dia 2
        ->assertSee('3 compras')
        ->assertSee('02/09/2026')
        ->assertSee('05/09/2026')
        ->assertDontSee('31/08/2026');
});

it('abre em hoje, sem botão de limpar, e aceita datas invertidas', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->travelTo('2026-09-15 10:00');

    $this->get(route('relatorio'))->assertOk()->assertSee('Hoje — 15/09/2026')->assertDontSee('Limpar filtro');
    $this->get(route('relatorio', ['periodo' => 'xyz']))->assertSee('Hoje — 15/09/2026');
    $this->get(route('relatorio', ['de' => '2026-09-10', 'ate' => '2026-09-01']))
        ->assertSee('De 01/09/2026 até 10/09/2026');
    $this->get(route('relatorio'))->assertSee('Nenhuma venda neste período.');
});

it('calcula os períodos prontos na data de hoje e mostra o botão de limpar', function (string $periodo, string $esperado) {
    $this->actingAs(User::factory()->create());
    $this->travelTo('2026-03-31 10:00');

    $this->get(route('relatorio', ['periodo' => $periodo, 'de' => '2020-01-01', 'ate' => '2020-01-02']))
        ->assertOk()
        ->assertSee($esperado)
        ->assertSee('Limpar filtro');
})->with([
    'últimos 7 dias' => ['7dias', 'Últimos 7 dias — de 25/03/2026 até 31/03/2026'],
    'este mês' => ['mes', 'Este mês — de 01/03/2026 até 31/03/2026'],
    'mês passado (fevereiro, sem estourar para março)' => ['mes-passado', 'Mês passado — de 01/02/2026 até 28/02/2026'],
    'personalizado' => ['personalizado', 'De 01/01/2020 até 02/01/2020'],
]);

it('não mostra venda de outro comércio', function () {
    $vizinho = User::factory()->create();
    $customer = Customer::factory()->recycle($vizinho)->create();
    $product = Product::factory()->recycle($vizinho)->create(['sale_price' => 99.00, 'stock_quantity' => 5]);
    $this->actingAs($vizinho);
    $this->post(route('sales.store'), ['customer_id' => $customer->id, 'items' => [['product_id' => $product->id, 'quantity' => 1]]]);

    $this->actingAs(User::factory()->create());
    $this->get(route('relatorio'))->assertDontSee('R$ 99,00')->assertSee('Nenhuma venda neste período.');
});
