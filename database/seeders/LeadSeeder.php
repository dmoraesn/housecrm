<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $statusList = Lead::STATUS;
        $origens = ['Site', 'Instagram', 'Facebook', 'Indicação', 'Anúncio', 'WhatsApp'];

        $corretores = User::whereHas('roles', fn($q) => $q->where('slug', 'corretor'))->get();

        if ($corretores->isEmpty()) {
            $this->command->warn('Nenhum corretor encontrado. Leads criados sem corretor.');
        }

        for ($i = 1; $i <= 20; $i++) {
            Lead::create([
                'nome' => "Lead {$i}",
                'email' => "lead{$i}@example.com",
                'telefone' => '(85) 9' . rand(8000, 9999) . '-' . rand(1000, 9999),
                'origem' => Arr::random($origens),
                'mensagem' => 'Interesse em imóvel ' . rand(100, 999),
                'status' => Arr::random($statusList),
                'user_id' => $corretores->count() > 0 ? $corretores->random()->id : null,
                'valor_interesse' => rand(100000, 1000000) / 100,
            ]);
        }

        $this->command->info('20 Leads gerados com sucesso!');
    }
}
