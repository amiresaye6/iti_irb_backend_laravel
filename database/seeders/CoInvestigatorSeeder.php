<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoInvestigator;

class CoInvestigatorSeeder extends Seeder
{
    public function run(): void
    {
        $coInvestigators = [
            1 => ['د. أحمد مصطفى', 'د. سارة كمال'],
            2 => ['د. يوسف الشناوي'],
            4 => ['د. منى زكي', 'د. رامي إمام', 'د. حسن يوسف'],
            5 => ['د. أحمد مصطفى', 'د. سارة كمال'],
            8 => ['د. هالة صدقي'],
            10 => ['د. عمر الفاروق'],
            13 => ['د. ليلى عثمان'],
        ];

        foreach ($coInvestigators as $appId => $names) {
            foreach ($names as $name) {
                CoInvestigator::create([
                    'application_id' => $appId,
                    'name' => $name,
                ]);
            }
        }
    }
}
