<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {

            // Já existem (não criar se já houver)
            if (!Schema::hasColumn('fluxos', 'base_calculo')) {
                $table->string('base_calculo')->default('avaliacao')->after('valor_avaliacao');
            }

            if (!Schema::hasColumn('fluxos', 'modo_calculo')) {
                $table->string('modo_calculo')->default('manual')->after('base_calculo');
            }

            if (!Schema::hasColumn('fluxos', 'financiamento_percentual')) {
                $table->decimal('financiamento_percentual', 5, 2)->default(80)->after('modo_calculo');
            }

            if (!Schema::hasColumn('fluxos', 'valor_financiado')) {
                $table->decimal('valor_financiado', 12, 2)->nullable()->after('financiamento_percentual');
            }

            if (!Schema::hasColumn('fluxos', 'entrada_minima')) {
                $table->decimal('entrada_minima', 12, 2)->nullable()->after('valor_bonus_descontos');
            }

            if (!Schema::hasColumn('fluxos', 'valor_parcela')) {
                $table->decimal('valor_parcela', 12, 2)->nullable()->after('parcelas_qtd');
            }

            if (!Schema::hasColumn('fluxos', 'total_parcelamento')) {
                $table->decimal('total_parcelamento', 12, 2)->nullable()->after('valor_parcela');
            }

            if (!Schema::hasColumn('fluxos', 'valor_total_entrada')) {
                $table->decimal('valor_total_entrada', 12, 2)->nullable()->after('total_parcelamento');
            }

            if (!Schema::hasColumn('fluxos', 'valor_restante')) {
                $table->decimal('valor_restante', 12, 2)->nullable()->after('valor_total_entrada');
            }

        });
    }

    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {

            $cols = [
                'base_calculo', 'modo_calculo', 'financiamento_percentual',
                'valor_financiado', 'entrada_minima', 'valor_parcela',
                'total_parcelamento', 'valor_total_entrada', 'valor_restante'
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('fluxos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
