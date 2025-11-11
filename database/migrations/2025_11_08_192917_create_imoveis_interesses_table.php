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
        Schema::create('imoveis_interesses', function (Blueprint $table) {
            $table->id();

            // 🔗 Chaves estrangeiras
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('imovel_id')->nullable()->constrained('imoveis')->onDelete('set null');

            // 🏷️ Campos opcionais adicionais
            $table->string('titulo')->nullable();
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imoveis_interesses');
    }
};
