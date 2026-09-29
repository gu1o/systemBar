<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use ScopedToUser;
    // Excluir produto apagava os itens de todas as vendas passadas (cascade da FK
    // de sale_items). Agora sai da lista e o histórico fica (§B4).
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'stock_alert',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'stock_alert' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
