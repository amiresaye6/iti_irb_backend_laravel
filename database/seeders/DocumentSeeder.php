<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documentTypes = [
            'protocol_review_app', 'conflict_of_interest', 'oral_presentaion',
            'pi_consent', 'patient_consent', 'research_procedures_approval', 'research_protocol',
            'research_alignment_with_research_plan'
        ];

        $filePaths = [
            'protocol_review_app' => 'uploads/seed/dummy_protocol.pdf',
            'conflict_of_interest' => 'uploads/seed/dummy_conflict.pdf',
            'oral_presentaion' => 'uploads/seed/dummy_oral_presentaion.pdf',
            'pi_consent' => 'uploads/seed/dummy_pi_consent.pdf',
            'patient_consent' => 'uploads/seed/dummy_patient_consent.pdf',
            'research_procedures_approval' => 'uploads/seed/dummy_patient_consent.pdf',
            'research_protocol' => 'uploads/seed/dummy_research_protocol.pdf',
            'research_alignment_with_research_plan' => 'uploads/seed/dummy_alignment.pdf'
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
