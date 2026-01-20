<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total_amount',
        'status',
    ];

    /**
     * Define o relacionamento: uma Venda (Sale) pertence a um Cliente (Customer).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Define o relacionamento: uma Venda (Sale) tem muitos Itens de Venda (SaleItem).
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
