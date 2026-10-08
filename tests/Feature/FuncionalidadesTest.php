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

    // Pelo filtro de status: o texto "Baixo Estoque" também é o rótulo da aba.
    $this->get(route('products.index', ['status' => 'baixo']))->assertSee('Cerveja Lata');
    $this->get(route('products.create'))->assertSee('Avisar quando o estoque chegar em');

    $this->patch(route('products.update', $user->products()->sole()), [
        'name' => 'Cerveja Lata', 'sale_price' => '5,00', 'stock_quantity' => 8, 'stock_alert' => 3,
    ])->assertRedirect(route('products.index'));

    expect($user->products()->sole()->stock_alert)->toBe(3);
    $this->get(route('products.index', ['status' => 'baixo']))->assertDontSee('Cerveja Lata');
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

    // Busca ao vivo (Livewire), sem recarregar a página.
    Livewire\Livewire::test(App\Livewire\Clientes::class)
        ->set('busca', 'joao')
        ->assertSee('Joao da Silva')
        ->assertDontSee('Maria Souza');

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

it('busca as compras pelo nome do cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $joao = Customer::factory()->recycle($user)->create(['name' => 'Joao da Silva']);

    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $user->sales()->create(['customer_id' => $joao->id, 'total_amount' => 10, 'status' => 'pending']);

    $this->get(route('sales.index', ['busca' => ' mar ']))
        ->assertSee('Maria Souza')
        ->assertDontSee('Joao da Silva');

    $this->get(route('sales.index', ['busca' => 'Pedro']))->assertSee('Nenhuma compra com esse filtro.');
});

it('filtra as compras sem recarregar a página', function () {
    $this->travelTo('2026-09-15 10:00'); // meio do mês: "este mês" com duas datas no resumo
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $joao = Customer::factory()->recycle($user)->create(['name' => 'Joao da Silva']);

    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $user->sales()->create(['customer_id' => $joao->id, 'total_amount' => 10, 'status' => 'paid']);

    \Livewire\Livewire::test(\App\Livewire\Vendas::class)
        ->assertSee('2 compras encontradas')
        ->set('busca', 'mar')
        ->assertSee('Maria Souza')->assertDontSee('Joao da Silva')
        ->call('buscar', '')
        ->set('situacao', 'paid')
        ->assertSee('Joao da Silva')->assertDontSee('Maria Souza')
        ->assertSee('1 compra encontrada')
        // As propriedades de/ate ficam vazias fora do personalizado; o resumo não pode usá-las.
        ->set('periodo', 'mes')
        ->assertSee('Este mês — de '.today()->startOfMonth()->format('d/m/Y'))
        // Personalizado começa no intervalo que estava na tela; "Limpar filtro" volta para hoje.
        ->set('periodo', 'personalizado')
        ->assertSet('de', today()->startOfMonth()->toDateString())
        ->call('limparPeriodo')
        ->assertSet('periodo', 'hoje')
        ->assertSet('de', '');
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

    // Data impossível não quebra a tela: vira hoje, nunca "sem limite".
    $this->get(route('sales.index', ['de' => '2026-02-31']))->assertOk()->assertDontSee('Maria Souza');
});

it('lembra o período das compras entre as abas e o limpar volta para hoje', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $this->travelTo('2026-09-02 10:00');
    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $this->travelTo('2026-10-05 10:00');

    $this->get(route('sales.index', ['periodo' => 'personalizado', 'de' => '2026-09-01', 'ate' => '2026-09-03']))
        ->assertSee('Maria Souza');

    // Trocar de aba (ou voltar pelo menu) chega sem ?periodo: reabre o personalizado.
    $this->get(route('sales.index', ['situacao' => 'pending']))
        ->assertSee('Maria Souza')
        ->assertSee('De 01/09/2026 até 03/09/2026');
    $this->get(route('sales.index'))->assertSee('Maria Souza');

    // Limpar manda ?periodo=hoje e passa a ser o lembrado.
    $this->get(route('sales.index', ['periodo' => 'hoje']))->assertDontSee('Maria Souza');
    $this->get(route('sales.index'))->assertDontSee('Maria Souza')->assertSee('Hoje — 05/10/2026');
});

it('abre as compras em hoje e filtra por período pronto', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $maria = Customer::factory()->recycle($user)->create(['name' => 'Maria Souza']);
    $joao = Customer::factory()->recycle($user)->create(['name' => 'Joao da Silva']);

    $this->travelTo('2026-08-20 20:00');
    $user->sales()->create(['customer_id' => $maria->id, 'total_amount' => 10, 'status' => 'pending']);
    $this->travelTo('2026-09-10 09:00');
    $user->sales()->create(['customer_id' => $joao->id, 'total_amount' => 10, 'status' => 'pending']);

    // Padrão: só hoje (nunca a tabela inteira). Fora do
    // padrão (?periodo=xyz) também cai em hoje.
    $this->get(route('sales.index'))->assertSee('Joao da Silva')->assertDontSee('Maria Souza');
    $this->get(route('sales.index', ['periodo' => 'todas']))->assertDontSee('Maria Souza');
    $this->travelTo('2026-09-11 09:00');
    $this->get(route('sales.index'))->assertSee('Nenhuma compra hoje.');

    $this->get(route('sales.index', ['periodo' => 'mes']))
        ->assertSee('Joao da Silva')->assertDontSee('Maria Souza');

    $this->get(route('sales.index', ['periodo' => 'mes-passado']))
        ->assertSee('Maria Souza')->assertDontSee('Joao da Silva');

    // As abas são links de verdade (abrir em nova aba funciona).
    $this->get(route('sales.index', ['periodo' => 'hoje', 'situacao' => 'pending']))
        ->assertSee(route('sales.index', ['situacao' => 'pending']), false);
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

it('filtra produtos pelo status do estoque, somando com a busca', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->recycle($user)->create(['name' => 'Cerveja Lata', 'stock_quantity' => 2, 'stock_alert' => 5]);
    Product::factory()->recycle($user)->create(['name' => 'Cerveja Garrafa', 'stock_quantity' => 50, 'stock_alert' => 5]);
    Product::factory()->recycle($user)->create(['name' => 'Detergente', 'stock_quantity' => 5, 'stock_alert' => 5]);

    $this->get(route('products.index', ['status' => 'baixo']))->assertOk()
        ->assertSee('Cerveja Lata')->assertSee('Detergente')->assertDontSee('Cerveja Garrafa');

    $this->get(route('products.index', ['status' => 'normal']))->assertOk()
        ->assertSee('Cerveja Garrafa')->assertDontSee('Cerveja Lata')->assertDontSee('Detergente');

    $this->get(route('products.index', ['status' => 'baixo', 'busca' => 'Cerveja']))
        ->assertSee('Cerveja Lata')->assertDontSee('Detergente')->assertDontSee('Cerveja Garrafa');

    // Sem recarregar (Livewire): trocar a aba mantém a busca, e vice-versa.
    \Livewire\Livewire::test(\App\Livewire\Produtos::class)
        ->set('busca', 'Cerveja')
        ->assertSee('Cerveja Lata')->assertSee('Cerveja Garrafa')->assertDontSee('Detergente')
        ->set('status', 'normal')
        ->assertSee('Cerveja Garrafa')->assertDontSee('Cerveja Lata')
        ->call('buscar', '  Detergente ')
        ->assertSet('busca', 'Detergente')
        ->assertSee('no nome está com estoque normal.');

    // Valor desconhecido = sem filtro.
    $this->get(route('products.index', ['status' => 'xyz']))->assertOk()
        ->assertSee('Cerveja Lata')->assertSee('Cerveja Garrafa');
});
