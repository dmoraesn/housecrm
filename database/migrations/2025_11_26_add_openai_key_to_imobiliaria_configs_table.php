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
        Schema::table('imobiliaria_configs', function (Blueprint $table) {
            // Usamos 'text' para ter espaço suficiente para chaves longas e 'nullable'
            // pois não é uma informação obrigatória para o funcionamento básico.
            $table->text('openai_api_key')->nullable()->after('creci'); // Adicione após um campo existente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imobiliaria_configs', function (Blueprint $table) {
            $table->dropColumn('openai_api_key');
        });
    }
};