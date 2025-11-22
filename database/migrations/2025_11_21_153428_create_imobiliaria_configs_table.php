<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imobiliaria_configs', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('creci')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->text('endereco_completo')->nullable();
            
            // Vamos usar o sistema de anexos do Orchid para o Logo, 
            // então não precisamos de um campo de texto para o caminho da imagem aqui,
            // mas é bom ter timestamps.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imobiliaria_configs');
    }
};