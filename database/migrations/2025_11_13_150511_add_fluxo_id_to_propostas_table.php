<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            $table->foreignId('fluxo_id')->nullable()->after('lead_id')->constrained('fluxos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            $table->dropForeign(['fluxo_id']);
            $table->dropColumn('fluxo_id');
        });
    }
};