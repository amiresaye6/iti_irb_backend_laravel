<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'phase' => fake()->randomElement(['initial', 'sample']),
            'amount' => fake()->randomFloat(2, 300, 1500),
            'provider' => fake()->randomElement(['Fawry', 'Paymob', 'InstaPay']),
            'transaction_reference' => fake()->unique()->bothify('??####'),
            'gateway_transaction_id' => fake()->numerify('#########'),
            'status' => 'completed',
            'gateway_response' => ['message' => 'Approved'],
            'paid_at' => now(),
        ];
    }
}
