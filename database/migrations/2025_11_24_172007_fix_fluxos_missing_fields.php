<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {

            // Adiciona lead_id se não existir
            if (!Schema::hasColumn('fluxos', 'lead_id')) {
                $table->foreignId('lead_id')
                      ->nullable()
                      ->after('cliente_id')
                      ->constrained('leads')
                      ->nullOnDelete();
            }

            // Adiciona construtora_id se não existir
            if (!Schema::hasColumn('fluxos', 'construtora_id')) {
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
        Schema::table('fluxos', function (Blueprint $table) {
            if (Schema::hasColumn('fluxos', 'lead_id')) {
                $table->dropForeign(['lead_id']);
                $table->dropColumn('lead_id');
            }

            if (Schema::hasColumn('fluxos', 'construtora_id')) {
                $table->dropForeign(['construtora_id']);
                $table->dropColumn('construtora_id');
            }
        });
    }
};
