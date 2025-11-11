<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            // Campos calculados pelo JS
            $table->decimal('valor_entrada', 15, 2)->default(0);
            $table->decimal('total_parcelamento', 15, 2)->default(0);
            $table->decimal('valor_restante', 15, 2)->default(0);

            // Balões dinâmicos
            $table->json('baloes_json')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            $table->dropColumn([
                'valor_entrada',
                'total_parcelamento',
                'valor_restante',
                'baloes_json'
            ]);
        });
    }
};