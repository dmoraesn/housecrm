<?php

namespace Database\Seeders;

use App\Models\AITemplate;
use App\Models\LeadStatus;
use Illuminate\Database\Seeder;

class AITemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AITemplate::DEFAULT_TEMPLATES as $status => $data) {
            AITemplate::updateOrCreate(
                ['lead_status' => $status],
                [
                    'nome' => ucfirst(str_replace('_', ' ', $status)),
                    'prompt' => $data['prompt'],
                    'max_tokens' => $data['max_tokens'],
                    'ativo' => true,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]
            );
        }
    }
}