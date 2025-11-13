<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            $table->string('base_calculo')->nullable()->after('valor_avaliacao'); // Após avaliação, lógico
        });
    }

    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            $table->dropColumn('base_calculo');
        });
    }
};