<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;
use Orchid\Platform\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ADMINISTRADOR (slug padrão do Orchid)
        $admin = Role::updateOrCreate(
            ['slug' => 'administrator'], // CORRETO
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
                ],
            ]
        );

        // CORRETOR
        $corretor = Role::updateOrCreate(
            ['slug' => 'corretor'],
            [
                'name' => 'Corretor',
                'permissions' => [
                    'platform.index' => true,
                    'platform.leads' => true,
                    'platform.leads.kanban' => true,
                    'platform.imoveis' => true,
                    'platform.propostas' => true,
                    'platform.comissoes' => true,
                    'platform.dashboard' => true,
                ],
            ]
        );

        // USUÁRIOS
        $users = [
            [
                'name' => 'Admin Master',
                'email' => 'admin@housecrm.com',
                'password' => 'password',
                'role' => $admin,
            ],
            [
                'name' => 'Corretor José',
                'email' => 'corretor@housecrm.com',
                'password' => 'password',
                'role' => $corretor,
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            $user->roles()->syncWithoutDetaching($data['role']);
        }

        $this->command->info('Admin: admin@housecrm.com / password');
    }
}