<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona o campo lead_id à tabela fluxos.
     */
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {

            // Adiciona coluna somente se não existir
            if (!Schema::hasColumn('fluxos', 'lead_id')) {
                $table->foreignId('lead_id')
                    ->nullable()
                    ->after('cliente_id') // Pode ajustar se desejar outra posição
                    ->constrained('leads')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Remove o campo lead_id e sua foreign key.
     */
    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {

            if (Schema::hasColumn('fluxos', 'lead_id')) {
                // Remove FK
                $table->dropForeign(['lead_id']);
                // Remove coluna
                $table->dropColumn('lead_id');
            }

        });
    }
};
