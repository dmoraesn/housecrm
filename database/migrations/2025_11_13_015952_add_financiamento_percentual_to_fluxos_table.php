<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            $table->decimal('financiamento_percentual', 5, 2)->nullable()->after('modo_calculo'); // Decimal para precisão, nullable para flexibilidade
        });
    }

    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            $table->dropColumn('financiamento_percentual');
        });
    }
};