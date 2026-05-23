<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'serial_number' => 'IRB-' . date('Y') . '-' . fake()->unique()->numerify('###'),
            'title' => fake()->sentence(8),
            'principal_investigator' => fake()->name(),
            'co_investigators' => [],
            'current_stage' => 'pending_admin',
            'is_blinded' => true,
        ];
    }
}
