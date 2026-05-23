<?php

namespace Database\Factories;

use App\Models\CoInvestigator;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoInvestigatorFactory extends Factory
{
    protected $model = CoInvestigator::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }
}
