<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {

            // adicionar lead_id se ainda não existir
            if (!Schema::hasColumn('propostas', 'lead_id')) {
                $table->unsignedBigInteger('lead_id')->nullable()->after('fluxo_id');
            }

            // adicionar data_assinatura se ainda não existir
            if (!Schema::hasColumn('propostas', 'data_assinatura')) {
                $table->date('data_assinatura')->nullable()->after('valor_restante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {

            if (Schema::hasColumn('propostas', 'lead_id')) {
                $table->dropColumn('lead_id');
            }

            if (Schema::hasColumn('propostas', 'data_assinatura')) {
                $table->dropColumn('data_assinatura');
            }
        });
    }
};
