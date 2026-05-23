<?php

namespace Database\Factories;

use App\Models\Signature;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Signature>
 */
class SignatureFactory extends Factory
{
    protected $model = Signature::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'signature_url' => 'uploads/signatures/dummy_signature.png',
        ];
    }
}
