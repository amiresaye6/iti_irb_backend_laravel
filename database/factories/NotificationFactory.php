<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'application_id' => Application::factory(),
            'message' => fake()->sentence(10),
            'channel' => 'system',
            'is_read' => false,
            'email_sent' => false,
        ];
    }
}
