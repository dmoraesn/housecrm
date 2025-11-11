<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Lead;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            // ===================================================================
            // COLUNAS PRINCIPAIS
            // ===================================================================
            $table->id();

            $table->string('nome');
            $table->string('email')->nullable()->unique();
            $table->string('telefone')->nullable();
            $table->string('origem')->nullable();

            $table->text('mensagem')->nullable();
            $table->text('observacoes')->nullable();

            // ===================================================================
            // STATUS DO FUNIL (ENUM com fluxo real)
            // ===================================================================
            $table->enum('status', Lead::STATUS)
                  ->default('novo')
                  ->comment('Fluxo: novo → qualificação → visita → negociação → fechamento → perdido');

            // ===================================================================
            // DADOS DE NEGÓCIO
            // ===================================================================
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Corretor responsável');

            $table->dateTime('data_contato')->nullable()
                  ->comment('Data do primeiro contato');

            $table->decimal('valor_interesse', 14, 2)->nullable()
                  ->comment('Valor estimado de interesse do lead');

            // ===================================================================
            // METADADOS
            // ===================================================================
            $table->timestamps();
            $table->softDeletes(); // Permite recuperação de leads excluídos

            // ===================================================================
            // ÍNDICES PARA PERFORMANCE
            // ===================================================================
            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['status', 'user_id']); // Filtro comum: "meus leads em negociação"
            $table->index('origem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};