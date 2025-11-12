<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a criação da tabela fluxos.
     */
    public function up(): void
    {
        Schema::create('fluxos', function (Blueprint $table) {
            $table->id();

            /**
             * 🔹 Importante:
             * A tabela clientes pode não existir ainda.
             * Vamos deixar o campo como unsignedBigInteger
             * e criar a FK apenas se a tabela existir.
             */
            $table->unsignedBigInteger('cliente_id')->nullable();

            $table->decimal('valor_imovel', 12, 2)->nullable();
            $table->decimal('valor_avaliacao', 12, 2)->nullable();
            $table->decimal('valor_entrada', 12, 2)->nullable();
            $table->decimal('valor_bonus_descontos', 12, 2)->nullable();
            $table->decimal('valor_assinatura_contrato', 12, 2)->nullable();
            $table->decimal('valor_na_chaves', 12, 2)->nullable();

            $table->json('baloes')->nullable();

            $table->integer('parcelas_qtd')->nullable();
            $table->decimal('valor_parcela', 12, 2)->nullable();
            $table->decimal('total_parcelamento', 12, 2)->nullable();

            $table->decimal('valor_total_entrada', 12, 2)->nullable();
            $table->decimal('valor_restante', 12, 2)->nullable();

            $table->text('observacao')->nullable();
            $table->string('status')->default('rascunho');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        /**
         * 🔹 Criação segura da foreign key
         * (somente se a tabela clientes existir no momento)
         */
        if (Schema::hasTable('clientes')) {
            Schema::table('fluxos', function (Blueprint $table) {
                $table->foreign('cliente_id')
                    ->references('id')
                    ->on('clientes')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('fluxos', function (Blueprint $table) {
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::table('fluxos', function (Blueprint $table) {
            if (Schema::hasColumn('fluxos', 'cliente_id')) {
                $table->dropForeign(['cliente_id']);
            }

            if (Schema::hasColumn('fluxos', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });

        Schema::dropIfExists('fluxos');
    }
};
