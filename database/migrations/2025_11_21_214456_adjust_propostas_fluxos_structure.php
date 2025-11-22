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
        // 1. Vincula a Proposta ao Fluxo
        Schema::table('propostas', function (Blueprint $table) {
            if (!Schema::hasColumn('propostas', 'fluxo_id')) {
                // nullOnDelete: Se o fluxo for apagado, a proposta não quebra, só perde o vínculo
                $table->foreignId('fluxo_id')
                      ->nullable()
                      ->after('lead_id')
                      ->constrained('fluxos')
                      ->nullOnDelete();
            }
        });

        // 2. Adiciona campos de Cartório e Chaves no Fluxo (onde fica a regra financeira)
        Schema::table('fluxos', function (Blueprint $table) {
            if (!Schema::hasColumn('fluxos', 'valor_cartorio')) {
                $table->decimal('valor_cartorio', 15, 2)->nullable()->default(0)->comment('Valor pago no repasse/cartório');
                $table->date('data_cartorio')->nullable()->comment('Previsão de repasse');
            }
            if (!Schema::hasColumn('fluxos', 'data_chaves')) {
                $table->date('data_chaves')->nullable()->comment('Previsão de entrega');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            $table->dropForeign(['fluxo_id']);
            $table->dropColumn('fluxo_id');
        });

        Schema::table('fluxos', function (Blueprint $table) {
            $table->dropColumn(['valor_cartorio', 'data_cartorio', 'data_chaves']);
        });
    }
};