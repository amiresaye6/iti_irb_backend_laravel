<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters: parents before children (foreign key dependencies).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ApplicationSeeder::class,
            CoInvestigatorSeeder::class,
            KeywordSeeder::class,
            DocumentSeeder::class,
            PaymentSeeder::class,
            ReviewSeeder::class,
            ReviewCommentSeeder::class,
            CertificateSeeder::class,
            NotificationSeeder::class,
            LogSeeder::class,
        ]);
    }
}
