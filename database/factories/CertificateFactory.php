<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'student_id' => User::factory()->student(),
            'manager_id' => User::factory()->manager(),
            'certificate_number' => 'CERT-' . date('Y') . '-' . fake()->unique()->numerify('#####'),
            'issued_to_name' => fake()->name(),
            'pdf_url' => 'uploads/seed/dummy_certificate.pdf',
        ];
    }
}
