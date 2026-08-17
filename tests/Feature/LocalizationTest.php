<?php

use App\Models\User;

it('roda em pt_BR e carrega as traduções do framework', function () {
    expect(app()->getLocale())->toBe('pt_BR')
        ->and(trans('auth.failed'))->toBe('E-mail ou senha incorretos. Verifique os dados e tente novamente.')
        ->and(trans('pagination.next'))->toBe('Próxima &raquo;');
});

it('traduz as mensagens de validação usando o nome do campo do formulário', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('products.store'), [])
        ->assertSessionHasErrors([
            'name' => 'Preencha o campo nome.',
            'sale_price' => 'Preencha o campo preço de venda.',
            'stock_quantity' => 'Preencha o campo quantidade em estoque.',
        ]);
});

it('usa as mensagens específicas do formulário de venda', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('sales.store'), [])
        ->assertSessionHasErrors([
            'customer_id' => 'Escolha um cliente para esta venda.',
            'items' => 'Adicione pelo menos um produto à venda.',
        ]);
});

it('não expõe a chave crua validation.unique quando o e-mail já está cadastrado', function () {
    User::factory()->create(['email' => 'maria@exemplo.com']);

    $this->post(route('register'), [
        'name' => 'Maria Souza',
        'email' => 'maria@exemplo.com',
        'password' => 'senha-bem-segura',
        'password_confirmation' => 'senha-bem-segura',
    ])->assertSessionHasErrors(['email' => 'Este e-mail já está sendo usado.']);
});
