<?php

namespace App\Models;

use App\Models\Concerns\ScopedToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;
    use ScopedToUser;

    protected $fillable = [
        'user_id',
        'customer_id',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Define o relacionamento: uma Venda (Sale) pertence a um Cliente (Customer).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        // withTrashed: o cliente arquivado tem que continuar aparecendo no histórico
        // da venda — é o nome que identifica a compra (§B3).
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * Define o relacionamento: uma Venda (Sale) tem muitos Itens de Venda (SaleItem).
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
