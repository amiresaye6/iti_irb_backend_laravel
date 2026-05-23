<?php

namespace Database\Factories;

use App\Models\Log;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'user_id' => User::factory(),
            'action' => fake()->sentence(5),
            'type' => fake()->randomElement(['submission', 'assignment', 'status_change', 'decision', 'certificate', 'document']),
        ];
    }
}
