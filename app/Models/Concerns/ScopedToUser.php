<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopedToUser
{
    /**
     * Restringe consultas ao usuário autenticado (multi-tenant por user_id).
     */
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
