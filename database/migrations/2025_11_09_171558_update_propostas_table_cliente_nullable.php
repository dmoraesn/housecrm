<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            // 1. Torna 'cliente' nullable (opcional)
            if (Schema::hasColumn('propostas', 'cliente')) {
                $table->string('cliente')->nullable()->change();
            }

            // 2. Garante que lead_id exista e tenha constraint (sem recriar)
            if (!Schema::hasColumn('propostas', 'lead_id')) {
                $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            } else {
                // Tenta adicionar constraint apenas se não existir
                \DB::statement('ALTER TABLE propostas ADD CONSTRAINT fk_propostas_lead_id FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE;');
            }
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            if (Schema::hasColumn('propostas', 'cliente')) {
                $table->string('cliente')->nullable(false)->change();
            }
        });
    }
};