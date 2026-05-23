<?php

namespace Database\Factories;

use App\Models\SampleSize;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SampleSize>
 */
class SampleSizeFactory extends Factory
{
    protected $model = SampleSize::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'sampler_id' => User::factory()->sampleOfficer(),
            'calculated_size' => fake()->numberBetween(50, 1000),
            'sample_amount' => fake()->randomFloat(2, 200, 1500),
            'notes' => fake()->sentence(10),
        ];
    }
}
