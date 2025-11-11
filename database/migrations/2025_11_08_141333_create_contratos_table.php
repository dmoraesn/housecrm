<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('imovel_id')->nullable()->constrained('imoveis')->onDelete('set null');
            $table->foreignId('comprador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('corretor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('tipo', ['compra', 'aluguel']);
            $table->decimal('valor_total', 12, 2);
            $table->decimal('valor_entrada', 12, 2)->nullable();
            $table->integer('parcelas')->nullable();
            $table->date('data_assinatura');
            $table->date('data_vencimento')->nullable();
            $table->enum('status', ['ativo', 'cancelado', 'finalizado'])->default('ativo');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};