<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chamada dos seeders principais do sistema
        $this->call([
            RolePermissionSeeder::class, // cria papéis e usuários
            LeadSeeder::class,           // popula leads aleatórios
            AITemplateSeeder::class,
        ]);
    }
}
