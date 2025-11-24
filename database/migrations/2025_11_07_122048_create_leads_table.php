<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment

            $table->string('nome');
            $table->string('email')->nullable()->unique();
            $table->string('telefone')->nullable();
            $table->string('origem')->nullable();

            $table->text('mensagem')->nullable();
            $table->text('observacoes')->nullable();

            $table->enum('status', ['novo', 'qualificacao', 'visita', 'negociacao', 'fechamento', 'perdido'])
                  ->default('novo')
                  ->comment('Fluxo: novo → qualificacao → visita → negociacao → fechamento → perdido');

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Corretor responsável');

            $table->dateTime('data_contato')->nullable()->comment('Data do primeiro contato');

            $table->decimal('valor_interesse', 14, 2)->nullable()->comment('Valor estimado de interesse do lead');

            $table->integer('order')->nullable()->default(1)->comment('Posição do lead no funil');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['status', 'user_id']);
            $table->index('origem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};