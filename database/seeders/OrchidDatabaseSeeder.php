<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orchid\Platform\Models\User;
use Orchid\Platform\Models\Role;
use Illuminate\Support\Facades\Hash;

class OrchidDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar role de administrador
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador', 'permissions' => [
                'platform.index' => true,
                'platform.systems' => true,
            ]]
        );

        // Criar usuário administrador
        $user = User::firstOrCreate(
            ['email' => 'admin@housecrm.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        $user->addRole($adminRole);

        $this->command->info('✅ Usuário administrador criado: admin@housecrm.com / password');
    }
}
