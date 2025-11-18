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
       Schema::table('propostas', function (Blueprint $table) {
    $table->decimal('valor_bonus_descontos', 10, 2)->default(0);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            //
        });
    }
};
