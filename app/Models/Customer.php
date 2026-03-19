<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;
    use ScopedToUser;

    protected $fillable = [
        'name',
        'phone',
        'notes',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define o relacionamento: um Cliente (Customer) tem muitas Vendas (Sales).
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
