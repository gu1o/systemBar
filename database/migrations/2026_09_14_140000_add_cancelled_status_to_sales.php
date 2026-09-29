<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §F4 — uma venda registrada por engano não tinha saída nenhuma: sem editar, sem
 * excluir, sem cancelar. O status vira `cancelled` em vez de a linha sumir — o
 * histórico é o registro do negócio, e faturamento apagado não se recupera.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('status', ['paid', 'pending', 'cancelled'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('status', ['paid', 'pending'])->default('pending')->change();
        });
    }
};
