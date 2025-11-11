<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['apartamento', 'casa', 'terreno', 'comercial']);
            $table->decimal('valor_venda', 12, 2)->nullable();
            $table->decimal('valor_aluguel', 12, 2)->nullable();
            $table->integer('quartos')->nullable();
            $table->integer('banheiros')->nullable();
            $table->integer('vagas')->nullable();
            $table->decimal('area', 8, 2)->nullable();
            $table->string('endereco');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('cep');
            $table->enum('status', ['disponivel', 'vendido', 'alugado', 'reservado'])->default('disponivel');
            $table->foreignId('construtora_id')->nullable()->constrained('construtoras')->onDelete('set null');
            $table->foreignId('corretor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis');
    }
};