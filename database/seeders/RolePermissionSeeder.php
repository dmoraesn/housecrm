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
        // 🔹 ADMINISTRADOR
        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrador',
                'permissions' => [
                    'platform.systems' => true,
                    'platform.index' => true,
                    'platform.leads' => true,
                    'platform.imoveis' => true,
                    'platform.contratos' => true,
                    'platform.propostas' => true,
                    'platform.comissoes' => true,
                    'platform.alugueis' => true,
                    'platform.construtoras' => true,
                    'platform.users' => true,
                ],
            ]
        );

        // 🔹 IMOBILIÁRIA
        $imobiliaria = Role::firstOrCreate(
            ['slug' => 'imobiliaria'],
            [
                'name' => 'Imobiliária',
                'permissions' => [
                    'platform.leads' => true,
                    'platform.imoveis' => true,
                    'platform.contratos' => true,
                    'platform.propostas' => true,
                    'platform.comissoes' => true,
                    'platform.alugueis' => true,
                    'platform.construtoras' => true,
                ],
            ]
        );

        // 🔹 CORRETOR
        $corretor = Role::firstOrCreate(
            ['slug' => 'corretor'],
            [
                'name' => 'Corretor',
                'permissions' => [
                    'platform.leads' => true,
                    'platform.imoveis' => true,
                    'platform.propostas' => true,
                    'platform.comissoes' => true,
                ],
            ]
        );

        // Usuários de exemplo
        $users = [
            [
                'name' => 'Admin Master',
                'email' => 'admin@housecrm.com',
                'password' => 'password',
                'role' => $admin,
            ],
            [
                'name' => 'Imobiliária Central',
                'email' => 'imob@housecrm.com',
                'password' => 'password',
                'role' => $imobiliaria,
            ],
            [
                'name' => 'Corretor José',
                'email' => 'corretor@housecrm.com',
                'password' => 'password',
                'role' => $corretor,
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                ]
            );

            // 🔸 Evita duplicar vínculos
            if (!$user->roles()->where('role_id', $data['role']->id)->exists()) {
                $user->addRole($data['role']);
            }
        }

        $this->command->info('✅ Roles e usuários verificados/criados com sucesso!');
    }
}
