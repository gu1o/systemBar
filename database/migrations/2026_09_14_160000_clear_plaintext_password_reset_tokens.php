<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * §T7 — a coluna `password_reset_tokens.email` passa a guardar o blind index no
 * lugar do endereço em texto puro. As linhas antigas ficam com a chave no formato
 * velho: nenhum token novo casaria com elas, e elas seguiriam expondo e-mails em
 * claro. Tokens de reset são descartáveis e expiram em 60 minutos — quem estiver
 * no meio de um "esqueci minha senha" pede outro link.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('password_reset_tokens')->delete();
    }

    public function down(): void
    {
        // Sem volta: os endereços em texto puro não devem ser restaurados.
    }
};
