<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * §T3 — `user_id` era nullable nas três tabelas de tenant. Uma linha com `user_id`
 * nulo fica invisível para todo mundo (o escopo global filtra por usuário) e órfã
 * para sempre. Fechar a porta no banco.
 */
return new class extends Migration
{
    private const TABELAS = ['products', 'customers', 'sales'];

    public function up(): void
    {
        // Órfãs existentes impediriam o NOT NULL com um erro de SQL ilegível. Falhar
        // antes, dizendo o que fazer: essas linhas precisam de dono, não de deleção
        // automática — são dados de alguém.
        foreach (self::TABELAS as $tabela) {
            $orfas = DB::table($tabela)->whereNull('user_id')->count();

            if ($orfas > 0) {
                throw new RuntimeException(
                    "A tabela {$tabela} tem {$orfas} linha(s) com user_id nulo. "
                    ."Atribua um dono a elas (ou apague-as) antes de rodar esta migration."
                );
            }
        }

        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->string('user_id', 36)->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->string('user_id', 36)->nullable()->change();
            });
        }
    }
};
