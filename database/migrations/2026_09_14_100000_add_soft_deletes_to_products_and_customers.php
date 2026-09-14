<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §B3/§B4 — exclusão passa a ser arquivamento.
 *
 * Antes: apagar um produto disparava o cascade de `sale_items` e apagava os itens de
 * todas as vendas passadas (com `sales.total_amount` intacto, então a soma deixava de
 * bater); apagar um cliente deixava `sales.customer_id` nulo e derrubava a lista de
 * Vendas inteira. Com `deleted_at`, nenhuma das duas FKs chega a disparar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
