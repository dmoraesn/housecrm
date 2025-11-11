<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            // Verifica se a coluna NÃO existe antes de adicionar
            if (!Schema::hasColumn('propostas', 'valor_entrada')) {
                $table->decimal('valor_entrada', 15, 2)->default(0)->after('valor_assinatura');
            }

            if (!Schema::hasColumn('propostas', 'total_parcelamento')) {
                $table->decimal('total_parcelamento', 15, 2)->default(0)->after('valor_parcela');
            }

            if (!Schema::hasColumn('propostas', 'valor_restante')) {
                $table->decimal('valor_restante', 15, 2)->default(0)->after('total_parcelamento');
            }

            if (!Schema::hasColumn('propostas', 'baloes_json')) {
                $table->json('baloes_json')->nullable()->after('valor_restante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            $table->dropColumn(['valor_entrada', 'total_parcelamento', 'valor_restante', 'baloes_json']);
        });
    }
};