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
            // Checa e adiciona status e nome_fantasia (caso as correções anteriores não tenham sido aplicadas)
            if (!Schema::hasColumn('construtoras', 'status')) {
                $table->boolean('status')->default(true)->after('email');
            }
            if (!Schema::hasColumn('construtoras', 'nome_fantasia')) {
                $table->string('nome_fantasia')->nullable()->after('nome');
            }
            
            // Adicionando os campos de Endereço/Extras ausentes (CADEIA DE ERROS)
            if (!Schema::hasColumn('construtoras', 'cep')) {
                $table->string('cep', 8)->nullable(); // Posição após email/status
            }
            if (!Schema::hasColumn('construtoras', 'logradouro')) {
                $table->string('logradouro', 255)->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'numero')) {
                $table->string('numero', 255)->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'complemento')) {
                $table->string('complemento', 255)->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'bairro')) {
                $table->string('bairro', 255)->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'cidade')) {
                $table->string('cidade', 255)->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'uf')) {
                $table->string('uf', 2)->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'socios')) {
                $table->text('socios')->nullable();
            }
            if (!Schema::hasColumn('construtoras', 'situacao')) {
                $table->string('situacao', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construtoras', function (Blueprint $table) {
            $table->dropColumn([
                'situacao', 'socios', 'uf', 'cidade', 'bairro', 'complemento', 'numero', 
                'logradouro', 'cep', 'status', 'nome_fantasia'
            ]);
        });
    }
};