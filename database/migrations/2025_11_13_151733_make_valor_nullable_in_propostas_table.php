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
            // Torna a coluna 'valor' nullable (evita erro de falta de valor)
            if (Schema::hasColumn('propostas', 'valor')) {
                $table->decimal('valor', 15, 2)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            if (Schema::hasColumn('propostas', 'valor')) {
                $table->decimal('valor', 15, 2)->nullable(false)->change();
            }
        });
    }
};