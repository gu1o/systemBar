<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

// §T7 — users.email é criptografado com blind index, mas a tabela de reset guardava
// o endereço em texto puro enquanto o token existisse.
it('não grava o e-mail em texto puro ao pedir recuperação de senha', function () {
    $user = User::factory()->create(['email' => 'dono@comercio.com.br']);

    $this->post('/forgot-password', ['email' => 'dono@comercio.com.br'])
        ->assertSessionHasNoErrors();

    $linha = DB::table('password_reset_tokens')->first();

    expect($linha)->not->toBeNull()
        ->and($linha->email)->not->toBe('dono@comercio.com.br')
        ->and($linha->email)->toBe(User::hashEmail('dono@comercio.com.br'));
});
