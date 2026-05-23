<?php

namespace Database\Factories;

use App\Models\Keyword;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Keyword>
 */
class KeywordFactory extends Factory
{
    protected $model = Keyword::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'serial_number' => null,
            'keyword' => fake()->word(),
        ];
    }
}
