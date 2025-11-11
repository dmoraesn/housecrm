<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alugueis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->onDelete('cascade');
            $table->foreignId('inquilino_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('corretor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('valor_mensal', 12, 2);
            $table->decimal('valor_caucao', 12, 2)->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->enum('status', ['ativo', 'encerrado', 'cancelado'])->default('ativo');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alugueis');
    }
};