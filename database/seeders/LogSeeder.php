<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Log;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            ['application_id' => 1, 'user_id' => 1, 'action' => 'تم تقديم البحث بنجاح ورفع المستندات', 'type' => 'submission', 'created_at' => '2026-03-01 10:00:00'],
            ['application_id' => 1, 'user_id' => 5, 'action' => 'مراجعة أولية وتوليد رقم تسلسلي للملف', 'type' => 'assignment', 'created_at' => '2026-03-01 12:00:00'],
            ['application_id' => 1, 'user_id' => 9, 'action' => 'اعتماد نهائي وإصدار شهادة IRB', 'type' => 'certificate', 'created_at' => '2026-03-15 10:00:00'],
            ['application_id' => 4, 'user_id' => 5, 'action' => 'تحديث حالة البحث إلى مرفوض بناءً على تقرير المراجعة', 'type' => 'decision', 'created_at' => '2026-02-28 13:00:00'],
            ['application_id' => 8, 'user_id' => 11, 'action' => 'تم تقديم البحث بنجاح', 'type' => 'submission', 'created_at' => '2026-01-15 10:30:00'],
            ['application_id' => 8, 'user_id' => 9, 'action' => 'اعتماد نهائي وإصدار شهادة IRB', 'type' => 'certificate', 'created_at' => '2026-02-01 10:00:00'],
        ];

        foreach ($logs as $log) {
            Log::create($log);
        }
    }
}
