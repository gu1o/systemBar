<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Bancos já criados com `cost_price` NOT NULL: torna a coluna nullable (alinha ao formulário opcional).
     * SQLite: `migrate:fresh` usa a migration de create já corrigida; DBs SQLite antigos exigem recreate manual.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE products MODIFY cost_price DECIMAL(10,2) NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE products ALTER COLUMN cost_price DROP NOT NULL');

            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        DB::table('products')->whereNull('cost_price')->update(['cost_price' => 0]);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE products MODIFY cost_price DECIMAL(10,2) NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE products ALTER COLUMN cost_price SET NOT NULL');
        }
    }
};
