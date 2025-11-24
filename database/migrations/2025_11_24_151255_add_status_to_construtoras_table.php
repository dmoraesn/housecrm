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
        Schema::table('construtoras', function (Blueprint $table) {
            // A posição 'after' é opcional, mas ajuda a manter o esquema limpo
            $table->boolean('status')->default(true)->after('email'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construtoras', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};