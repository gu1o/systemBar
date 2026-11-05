<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * §T4 — as regras de produto estavam repetidas literalmente em `store()` e
 * `update()`. Duas cópias divergem no primeiro ajuste, e a divergência aparece
 * como "cadastrar aceita, editar recusa" na cara do usuário.
 */
class ProductRequest extends FormRequest
{
    /**
     * O preço chega do formulário no formato pt-BR (1.234,56) por causa da máscara.
     * Normalizar antes de validar, não dentro do controller.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sale_price' => $this->normalizarDinheiro($this->input('sale_price')) ?? '',
            'cost_price' => $this->normalizarDinheiro($this->input('cost_price')),
            // Campo opcional, mas a coluna é NOT NULL: em branco chegava como null e o
            // INSERT estourava. Vazio = o mesmo 5 que o formulário sugere (default da coluna).
            'stock_alert' => $this->filled('stock_alert') ? $this->input('stock_alert') : 5,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert' => 'nullable|integer|min:0',
        ];
    }

    private function normalizarDinheiro(?string $valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        $v = str_replace(['R$', ' ', "\xC2\xA0"], '', trim($valor));

        if (str_contains($v, ',')) {
            $v = str_replace(['.', ','], ['', '.'], $v);
        }

        return $v;
    }
}
