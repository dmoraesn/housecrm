<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->string('corretor');
            $table->string('imovel');
            $table->decimal('valor', 12, 2);
            $table->decimal('percentual', 5, 2)->nullable();
            $table->string('status')->default('pendente');
            $table->date('data_pagamento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
