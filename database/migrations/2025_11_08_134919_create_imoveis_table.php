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
        Schema::create('imoveis', function (Blueprint $table) {
            // ===================================================================
            // IDENTIFICAÇÃO
            // ===================================================================
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();

            // ===================================================================
            // CARACTERÍSTICAS PRINCIPAIS
            // ===================================================================
            $table->enum('tipo', [
                'apartamento',
                'casa',
                'terreno',
                'comercial',
            ])->comment('Tipo do imóvel');

            $table->decimal('valor_venda', 12, 2)->nullable()->comment('Valor de venda do imóvel');
            $table->decimal('valor_aluguel', 12, 2)->nullable()->comment('Valor de aluguel do imóvel');

            $table->integer('quartos')->nullable()->comment('Número de quartos');
            $table->integer('banheiros')->nullable()->comment('Número de banheiros');
            $table->integer('vagas')->nullable()->comment('Vagas de garagem');

            $table->decimal('area', 8, 2)->nullable()->comment('Área total em m²');

            // ===================================================================
            // LOCALIZAÇÃO
            // ===================================================================
            $table->string('endereco');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('cep', 9);

            // ===================================================================
            // STATUS DO IMÓVEL
            // ===================================================================
            $table->enum('status', [
                'disponivel',
                'vendido',
                'alugado',
                'reservado',
            ])
            ->default('disponivel')
            ->comment('Situação atual do imóvel');

            // ===================================================================
            // RELACIONAMENTOS
            // ===================================================================
            $table->foreignId('construtora_id')
                  ->nullable()
                  ->constrained('construtoras')
                  ->onDelete('set null')
                  ->comment('Construtora associada ao imóvel');

            $table->foreignId('corretor_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Corretor responsável pelo imóvel');

            // ===================================================================
            // METADADOS
            // ===================================================================
            $table->timestamps();
            $table->softDeletes(); // mantém histórico de imóveis removidos

            // ===================================================================
            // ÍNDICES PARA PERFORMANCE
            // ===================================================================
            $table->index('tipo');
            $table->index('status');
            $table->index(['cidade', 'estado']);
            $table->index('corretor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imoveis');
    }
};
