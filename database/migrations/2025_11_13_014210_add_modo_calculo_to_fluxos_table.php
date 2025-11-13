<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            // Adiciona modo_calculo (string: 'percentual' ou 'manual')
            $table->string('modo_calculo')->nullable()->after('valor_avaliacao');

            // Se base_calculo existir no form mas não no DB, adicione aqui
            // $table->string('base_calculo')->nullable()->after('modo_calculo');

            // Verifique e adicione outros campos se faltarem (baseado no form/SQL)
            // Ex.: se entrada_minima etc. não existirem, adicione como decimal(15,2)
            // $table->decimal('entrada_minima', 15, 2)->nullable();
            // $table->decimal('valor_parcela', 15, 2)->nullable();
            // ... (adicione conforme necessidade, mas erro atual é só modo_calculo)
        });
    }

    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            $table->dropColumn('modo_calculo');
            // $table->dropColumn('base_calculo');
            // ... (drop outros se adicionados)
        });
    }
};