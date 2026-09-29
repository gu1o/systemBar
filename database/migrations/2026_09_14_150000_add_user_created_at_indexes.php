<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §T8 — toda listagem filtra por `user_id` e ordena por `created_at` (`->latest()`),
 * e só existia índice em `user_id`: o banco filtrava pelo índice e ordenava em
 * memória. Irrelevante no volume de hoje, barato de resolver antes de não ser.
 */
return new class extends Migration
{
    private const TABELAS = ['products', 'customers', 'sales'];

    public function up(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                $table->index(['user_id', 'created_at'], "{$tabela}_user_id_created_at_index");
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                $table->dropIndex("{$tabela}_user_id_created_at_index");
            });
        }
    }
};
