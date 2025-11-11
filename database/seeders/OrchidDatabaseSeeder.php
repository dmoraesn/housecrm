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
        $admin = Role::updateOrCreate(
            ['slug' => 'administrator'],
            [
                'name' => 'Administrador',
                'permissions' => [
                    'platform.index' => true,
                    'platform.systems' => true,
                    'platform.systems.users' => true,
                    'platform.systems.roles' => true,
                    'platform.leads' => true,
                    'platform.leads.kanban' => true,
                    'platform.imoveis' => true,
                    'platform.contratos' => true,
                    'platform.propostas' => true,
                    'platform.comissoes' => true,
                    'platform.alugueis' => true,
                    'platform.construtoras' => true,
                    'platform.dashboard' => true,
                    'platform.fluxo' => true,
                ],
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'admin@housecrm.com'],
            [
                'name' => 'Admin Master',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (!$user->roles()->where('role_id', $admin->id)->exists()) {
            $user->roles()->attach($admin->id);
        }

        $this->command->info('✅ Usuário administrador atualizado: admin@housecrm.com / password');
    }
}
