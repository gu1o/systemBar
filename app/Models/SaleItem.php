<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Valor da linha do comprovante. Calculado, não gravado: subtotal é sempre
     * quantidade × preço unitário, e coluna derivada é coluna que dessincroniza.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn (): float => $this->quantity * $this->unit_price);
    }

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
