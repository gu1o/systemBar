<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // Link já usado ou vencido: avisa logo, em vez de deixar preencher o form à toa.
        $user = User::where('email_hash', (string) $request->email)->first();

        if (! $user || ! Password::tokenExists($user, (string) $request->route('token'))) {
            return redirect()->route('password.request')
                ->withErrors(['email' => __(Password::INVALID_TOKEN)]);
        }

        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // O link do e-mail traz o blind index (User::getEmailForPasswordReset), não o
        // endereço — a tela não mostra nem pede e-mail. O broker procura por e-mail
        // real, então trocamos o hash pelo endereço do dono antes de chamá-lo.
        $email = User::where('email_hash', $request->email)->first()?->email;

        if ($email === null) {
            return back()->withErrors(['email' => __(Password::INVALID_TOKEN)]);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            ['email' => $email] + $request->only('password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }
}
