<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            // Garante que TODOS os campos existam (não falha se já existirem)

            // Relacionamento
            if (!Schema::hasColumn('propostas', 'lead_id')) {
                $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            }

            // Status
            if (!Schema::hasColumn('propostas', 'status')) {
                $table->enum('status', ['rascunho', 'enviada', 'aceita', 'recusada'])->default('rascunho');
            }

            // Campos do simulador
            if (!Schema::hasColumn('propostas', 'valor_avaliacao')) {
                $table->decimal('valor_avaliacao', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('propostas', 'valor_real')) {
                $table->decimal('valor_real', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('propostas', 'valor_financiado')) {
                $table->decimal('valor_financiado', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'descontos')) {
                $table->decimal('descontos', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'valor_assinatura')) {
                $table->decimal('valor_assinatura', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'valor_parcela')) {
                $table->decimal('valor_parcela', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'num_parcelas')) {
                $table->unsignedInteger('num_parcelas')->default(0);
            }

            // Campos calculados pelo JS
            if (!Schema::hasColumn('propostas', 'valor_entrada')) {
                $table->decimal('valor_entrada', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'total_parcelamento')) {
                $table->decimal('total_parcelamento', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'valor_restante')) {
                $table->decimal('valor_restante', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('propostas', 'baloes_json')) {
                $table->json('baloes_json')->nullable();
            }

            // Timestamps (se não existirem)
            if (!Schema::hasColumn('propostas', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // Remove apenas os campos que adicionamos (seguro)
        Schema::table('propostas', function (Blueprint $table) {
            $table->dropColumn([
                'valor_entrada',
                'total_parcelamento',
                'valor_restante',
                'baloes_json',
                'valor_assinatura',
                'valor_parcela',
                'num_parcelas',
                'descontos',
                'valor_financiado',
                'valor_real',
                'valor_avaliacao',
                'status',
            ]);
        });
    }
};