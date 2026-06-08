<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $certificates = [
            ['application_id' => 1, 'student_id' => 1, 'manager_id' => 9, 'certificate_number' => 'CERT-2026-10045', 'issued_to_name' => 'د. عمر الفاروق', 'pdf_url' => 'uploads/seed/dummy_certificate.pdf', 'issued_at' => '2026-03-15 10:00:00'],
            ['application_id' => 8, 'student_id' => 11, 'manager_id' => 9, 'certificate_number' => 'CERT-2026-10046', 'issued_to_name' => 'د. سلمى رضا', 'pdf_url' => 'uploads/seed/dummy_certificate.pdf', 'issued_at' => '2026-02-01 10:00:00'],
        ];

        foreach ($certificates as $cert) {
            Certificate::create($cert);
        }
    }
}
