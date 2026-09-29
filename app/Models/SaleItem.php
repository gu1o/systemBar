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
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    /**
     * Valor da linha do comprovante. Calculado, não gravado: subtotal é sempre
     * quantidade × preço unitário, e coluna derivada é coluna que dessincroniza.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn (): float => $this->quantity * $this->unit_price);
    }

    /**
     * Lucro da linha com o custo gravado na venda. Null quando o produto não tinha
     * preço de custo: lucro desconhecido não é lucro igual ao preço inteiro.
     */
    protected function lucro(): Attribute
    {
        return Attribute::get(fn (): ?float => $this->unit_cost === null
            ? null
            : $this->quantity * ($this->unit_price - $this->unit_cost));
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
        // withTrashed: produto arquivado continua nomeado no comprovante antigo (§B4).
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
