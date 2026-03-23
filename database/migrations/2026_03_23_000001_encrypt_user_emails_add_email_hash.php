<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL: UNIQUE on VARCHAR + change to TEXT fails (1170). Drop index first.
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('email')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email_hash', 64)->nullable();
        });

        $key = config('app.blind_index_key') ?: config('app.key');

        foreach (DB::table('users')->cursor() as $row) {
            $normalized = Str::lower(trim((string) $row->email));
            DB::table('users')->where('id', $row->id)->update([
                'email_hash' => hash_hmac('sha256', $normalized, $key),
                'email' => encrypt($normalized),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email_hash', 64)->nullable(false)->change();
            $table->unique('email_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (DB::table('users')->cursor() as $row) {
            $raw = $row->email;
            try {
                $plain = decrypt($raw);
            } catch (\Throwable) {
                $plain = (string) $raw;
            }
            DB::table('users')->where('id', $row->id)->update(['email' => $plain]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email_hash']);
            $table->dropColumn('email_hash');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 255)->change();
            $table->unique('email');
        });
    }
};
