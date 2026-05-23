<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'role' => fake()->randomElement(['student', 'admin', 'sample_officer', 'reviewer', 'manager', 'super_admin']),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => \Illuminate\Support\Str::random(10),
            'national_id' => fake()->numerify('##############'),
            'phone_number' => fake()->numerify('01#########'),
            'faculty' => fake()->randomElement(['كلية الطب', 'كلية الصيدلة', 'كلية الأسنان', 'كلية التمريض']),
            'department' => fake()->randomElement(['الجراحة العامة', 'الأطفال', 'الباطنة', 'النساء والتوليد']),
            'id_front_url' => null,
            'id_back_url' => null,
            'is_active' => true,
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
            'id_front_url' => 'uploads/seed/dummy_id_front.jpg',
            'id_back_url' => 'uploads/seed/dummy_id_back.jpg',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'faculty' => null,
            'department' => null,
            'id_front_url' => null,
            'id_back_url' => null,
        ]);
    }

    public function reviewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'reviewer',
            'id_front_url' => null,
            'id_back_url' => null,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
            'id_front_url' => null,
            'id_back_url' => null,
        ]);
    }

    public function sampleOfficer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'sample_officer',
            'faculty' => null,
            'department' => null,
            'id_front_url' => null,
            'id_back_url' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'faculty' => null,
            'department' => null,
            'id_front_url' => null,
            'id_back_url' => null,
        ]);
    }
}
