<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\User;

// §B2 — vender mais do que existe deixava o estoque negativo, sem aviso nenhum.
it('recusa a venda quando falta estoque, nomeando o produto e quanto tem', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create([
        'name' => 'Coca Cola 2L', 'stock_quantity' => 3,
    ]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 100]],
    ])->assertSessionHasErrors(['items' => 'Você tem apenas 3 unidades de Coca Cola 2L em estoque.']);

    expect($user->sales()->count())->toBe(0)
        ->and($product->fresh()->stock_quantity)->toBe(3);
});

// A validação olha a quantidade consolidada: linha a linha, 3 + 3 passaria num
// produto com 5 em estoque.
it('soma as linhas do mesmo produto antes de conferir o estoque', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create([
        'name' => 'Salgadinho', 'stock_quantity' => 5,
    ]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 3],
            ['product_id' => $product->id, 'quantity' => 3],
        ],
    ])->assertSessionHasErrors('items');

    expect($product->fresh()->stock_quantity)->toBe(5);
});

it('deixa vender exatamente o que tem em estoque', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create([
        'sale_price' => 10.00, 'stock_quantity' => 4,
    ]);

    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 4]],
    ])->assertRedirect(route('sales.index'));

    expect($product->fresh()->stock_quantity)->toBe(0)
        ->and((float) $user->sales()->sole()->total_amount)->toBe(40.00);
});

// §B5 — sem transação, uma falha no meio do loop deixava venda de R$ 0,00 no banco,
// itens parciais e estoque já baixado.
it('não deixa venda nem baixa de estoque se algo falhar no meio', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->recycle($user)->create();
    $product = Product::factory()->recycle($user)->create(['stock_quantity' => 10]);

    SaleItem::creating(function () {
        throw new RuntimeException('falha simulada no meio da venda');
    });

    try {
        $this->withoutExceptionHandling()->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);
    } catch (RuntimeException) {
        // esperado: é o ponto de falha que a transação tem que desfazer
    }

    expect($user->sales()->count())->toBe(0)
        ->and($product->fresh()->stock_quantity)->toBe(10);
});
