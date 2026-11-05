<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $hash = User::hashEmail($user->email);
        $response = $this->get('/reset-password/'.$notification->token.'?email='.$hash);

        // O hash vai só em campo oculto; nenhum campo de e-mail visível.
        $response->assertStatus(200)
            ->assertSee('type="hidden" name="email" value="'.$hash.'"', false)
            ->assertDontSee('type="email"', false);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => User::hashEmail($user->email), // o que o link do e-mail traz
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('o link de redefinição só funciona uma vez', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $dados = [
            'token' => $notification->token,
            'email' => User::hashEmail($user->email),
            'password' => 'nova-senha-1',
            'password_confirmation' => 'nova-senha-1',
        ];

        $this->post('/reset-password', $dados)->assertSessionHasNoErrors();

        $this->post('/reset-password', ['password' => 'nova-senha-2', 'password_confirmation' => 'nova-senha-2'] + $dados)
            ->assertSessionHasErrors('email');

        expect(\Illuminate\Support\Facades\Hash::check('nova-senha-1', $user->fresh()->password))->toBeTrue();

        // Abrir o link de novo já manda pedir outro, sem mostrar o formulário.
        $this->get('/reset-password/'.$dados['token'].'?email='.$dados['email'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        return true;
    });
});
