<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {

            // Só cria se não existir ainda
            if (!Schema::hasColumn('propostas', 'construtora_id')) {

                $table->foreignId('construtora_id')
                      ->nullable()
                      ->after('lead_id')
                      ->constrained('construtoras')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {

            if (Schema::hasColumn('propostas', 'construtora_id')) {
                $table->dropForeign(['construtora_id']);
                $table->dropColumn('construtora_id');
            }
        });
    }
};
