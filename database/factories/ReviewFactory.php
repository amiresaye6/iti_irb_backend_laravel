<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'reviewer_id' => User::factory()->reviewer(),
            'assigned_by' => User::factory()->admin(),
            'assignment_status' => 'awaiting_acceptance',
            'decision' => 'pending',
            'refusal_reason' => null,
            'reviewed_at' => null,
        ];
    }
}
