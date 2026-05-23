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
            ['application_id' => 1, 'user_id' => 6, 'action' => 'تم حساب حجم العينة (150)', 'type' => 'status_change', 'created_at' => '2026-03-03 09:00:00'],
            ['application_id' => 1, 'user_id' => 11, 'action' => 'اعتماد نهائي وإصدار شهادة IRB', 'type' => 'certificate', 'created_at' => '2026-03-15 10:00:00'],
            ['application_id' => 4, 'user_id' => 5, 'action' => 'تحديث حالة البحث إلى مرفوض بناءً على تقرير المراجعة', 'type' => 'decision', 'created_at' => '2026-02-28 13:00:00'],
            ['application_id' => 8, 'user_id' => 13, 'action' => 'تم تقديم البحث بنجاح', 'type' => 'submission', 'created_at' => '2026-01-15 10:30:00'],
            ['application_id' => 8, 'user_id' => 7, 'action' => 'تم حساب حجم العينة (1000)', 'type' => 'status_change', 'created_at' => '2026-01-20 09:45:00'],
            ['application_id' => 8, 'user_id' => 11, 'action' => 'اعتماد نهائي وإصدار شهادة IRB', 'type' => 'certificate', 'created_at' => '2026-02-01 10:00:00'],
        ];

        foreach ($logs as $log) {
            Log::create($log);
        }
    }
}
