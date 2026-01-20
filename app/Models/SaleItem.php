<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    /**
     * Define o relacionamento: um Item de Venda (SaleItem) pertence a uma Venda (Sale).
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Define o relacionamento: um Item de Venda (SaleItem) pertence a um Produto (Product).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
