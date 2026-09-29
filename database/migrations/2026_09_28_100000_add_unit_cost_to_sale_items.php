<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Margem de lucro — o custo precisa ficar gravado na venda, como o preço já fica
 * (`unit_price`). Lendo `products.cost_price` na hora da conta, o dono que atualiza
 * o custo de um produto reescreveria o lucro de todos os meses passados.
 *
 * Nullable: produto sem preço de custo não tem lucro conhecido. Tratar como zero
 * inflaria o lucro com a venda inteira.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->after('unit_price');
        });

        // Vendas antigas: o custo atual do produto é a melhor informação que existe.
        DB::statement('UPDATE sale_items SET unit_cost = (SELECT cost_price FROM products WHERE products.id = sale_items.product_id)');
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
