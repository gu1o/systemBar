<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Restringe consultas ao usuário autenticado (multi-tenant por user_id).
 *
 * ATENÇÃO — este escopo FALHA ABERTO (§T2). Ele só se aplica quando há usuário
 * autenticado. Em console, fila ou scheduler não há autenticação, logo não há
 * escopo: `Product::all()` devolve as linhas de **todos** os comércios.
 *
 * Isso é proposital e necessário — migrations, seeders e `php artisan tinker`
 * precisam enxergar tudo —, mas vira vazamento entre comércios no primeiro
 * comando ou job que consultar estes modelos sem filtrar.
 *
 * Regra para quem escrever o primeiro deles: **nunca** consulte `Product`,
 * `Customer` ou `Sale` direto num contexto sem requisição. Parta sempre do dono:
 *
 *     foreach (User::cursor() as $user) {
 *         $user->products()->where(...)   // escopo pela relação, não pelo Auth
 *     }
 *
 * Se um dia existirem vários jobs assim, a saída é trocar isto por um escopo
 * explícito (`Product::forUser($user)`) e proibir a consulta sem dono.
 */
trait ScopedToUser
{
    protected static function bootScopedToUser(): void
    {
        static::addGlobalScope('scopedToUser', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where(
                    $builder->getModel()->getTable().'.user_id',
                    Auth::id()
                );
            }
        });
    }
}
