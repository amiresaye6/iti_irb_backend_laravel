<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documentTypes = [
            'protocol', 'conflict_of_interest', 'irb_checklist',
            'pi_consent', 'patient_consent', 'photos_biopsies_consent', 'protocol_review_app'
        ];

        $filePaths = [
            'protocol' => 'uploads/seed/dummy_protocol.pdf',
            'conflict_of_interest' => 'uploads/seed/dummy_conflict.pdf',
            'irb_checklist' => 'uploads/seed/dummy_checklist.pdf',
            'pi_consent' => 'uploads/seed/dummy_pi_consent.pdf',
            'patient_consent' => 'uploads/seed/dummy_patient_consent.pdf',
            'photos_biopsies_consent' => 'uploads/seed/dummy_patient_consent.pdf',
            'protocol_review_app' => 'uploads/seed/dummy_certificate.pdf',
        ];

        // Create documents for all 15 applications
        for ($appId = 1; $appId <= 15; $appId++) {
            foreach ($documentTypes as $type) {
                Document::create([
                    'application_id' => $appId,
                    'document_type' => $type,
                    'file_path' => $filePaths[$type],
                ]);
            }
        }
    }
}
