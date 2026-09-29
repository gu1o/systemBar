<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('email') && $user->email !== null && $user->email !== '') {
                $user->email = Str::lower(trim($user->email));
                $user->email_hash = static::hashEmail($user->email);
            }
        });
    }

    /**
     * HMAC-SHA256 blind index for exact email lookup (login, uniqueness).
     */
    public static function hashEmail(?string $email): string
    {
        if ($email === null || $email === '') {
            return '';
        }

        $normalized = Str::lower(trim($email));
        $key = config('app.blind_index_key') ?: config('app.key');

        return hash_hmac('sha256', $normalized, $key);
    }

    /**
     * §T7 — o que vai para `password_reset_tokens.email`.
     *
     * `users.email` é criptografado e consultado por blind index, mas a tabela de
     * reset guardava o endereço em **texto puro** como chave primária: cada "esqueci
     * minha senha" gravava o e-mail em claro no banco enquanto o token existisse.
     *
     * Devolvendo o blind index, a tabela passa a guardar o mesmo hash — o broker
     * grava e consulta pelo retorno deste método, então os dois lados batem. O link
     * do e-mail continua sendo enviado para o endereço real (`routeNotificationForMail`).
     */
    public function getEmailForPasswordReset(): string
    {
        return static::hashEmail($this->email);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
