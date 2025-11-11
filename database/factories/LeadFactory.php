<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'telefone' => '(85) 9' . rand(8000, 9999) . '-' . rand(1000, 9999),
            'origem' => $this->faker->randomElement(Lead::ORIGENS),
            'mensagem' => $this->faker->sentence,
            'status' => $this->faker->randomElement(Lead::STATUS),
            'user_id' => User::role('corretor')->inRandomOrder()->first()?->id,
            'data_contato' => $this->faker->optional()->dateTimeThisMonth,
            'valor_interesse' => $this->faker->optional()->randomFloat(2, 200000, 2000000),
        ];
    }
}