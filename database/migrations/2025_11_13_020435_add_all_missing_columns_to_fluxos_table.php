<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            if (!Schema::hasColumn('fluxos', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            }
            if (!Schema::hasColumn('fluxos', 'valor_imovel')) {
                $table->decimal('valor_imovel', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_avaliacao')) {
                $table->decimal('valor_avaliacao', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'base_calculo')) {
                $table->string('base_calculo')->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'modo_calculo')) {
                $table->string('modo_calculo')->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'financiamento_percentual')) {
                $table->decimal('financiamento_percentual', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_financiado')) {
                $table->decimal('valor_financiado', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_bonus_descontos')) {
                $table->decimal('valor_bonus_descontos', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'entrada_minima')) {
                $table->decimal('entrada_minima', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_assinatura_contrato')) {
                $table->decimal('valor_assinatura_contrato', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_na_chaves')) {
                $table->decimal('valor_na_chaves', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'baloes')) {
                $table->json('baloes')->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'parcelas_qtd')) {
                $table->integer('parcelas_qtd')->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_parcela')) {
                $table->decimal('valor_parcela', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'total_parcelamento')) {
                $table->decimal('total_parcelamento', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_total_entrada')) {
                $table->decimal('valor_total_entrada', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'valor_restante')) {
                $table->decimal('valor_restante', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'observacao')) {
                $table->text('observacao')->nullable();
            }
            if (!Schema::hasColumn('fluxos', 'status')) {
                $table->string('status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            // Drop apenas se existirem, para consistência
            $columns = [
                'lead_id', 'valor_imovel', 'valor_avaliacao', 'base_calculo', 'modo_calculo',
                'financiamento_percentual', 'valor_financiado', 'valor_bonus_descontos',
                'entrada_minima', 'valor_assinatura_contrato', 'valor_na_chaves', 'baloes',
                'parcelas_qtd', 'valor_parcela', 'total_parcelamento', 'valor_total_entrada',
                'valor_restante', 'observacao', 'status'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('fluxos', $column)) {
                    $table->dropColumn($column);
                }
            }
            if (Schema::hasColumn('fluxos', 'lead_id')) {
                $table->dropForeign(['lead_id']);
            }
        });
    }
};