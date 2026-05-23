<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'document_type' => fake()->randomElement([
                'research', 'protocol', 'conflict_of_interest', 'irb_checklist',
                'pi_consent', 'patient_consent', 'photos_biopsies_consent', 'protocol_review_app'
            ]),
            'file_path' => 'uploads/seed/dummy_protocol.pdf',
        ];
    }
}
