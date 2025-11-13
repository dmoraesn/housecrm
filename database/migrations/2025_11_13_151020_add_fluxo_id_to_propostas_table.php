<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            // Adiciona a coluna fluxo_id se ainda não existir
            if (!Schema::hasColumn('propostas', 'fluxo_id')) {
                $table->foreignId('fluxo_id')
                      ->nullable()
                      ->after('lead_id')
                      ->constrained('fluxos')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            if (Schema::hasColumn('propostas', 'fluxo_id')) {
                $table->dropForeign(['fluxo_id']);
                $table->dropColumn('fluxo_id');
            }
        });
    }
};