<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            ['user_id' => 2, 'application_id' => 2, 'message' => 'بحثك (IRB-2026-002) يحتاج إلى تعديلات بناءً على ملاحظات المراجعة الفنية. يرجى مراجعة التعليقات وتحديث المستندات.', 'channel' => 'system', 'is_read' => false, 'email_sent' => true, 'created_at' => '2026-04-18 09:05:00'],
            ['user_id' => 4, 'application_id' => 4, 'message' => 'تم رفض بحثك (IRB-2026-004). يرجى مراجعة أسباب الرفض في تفاصيل البحث.', 'channel' => 'system', 'is_read' => true, 'email_sent' => true, 'created_at' => '2026-02-28 12:05:00'],
            ['user_id' => 1, 'application_id' => 1, 'message' => 'تهانينا! تم اعتماد بحثك (IRB-2026-001) نهائياً وإصدار شهادة IRB.', 'channel' => 'system', 'is_read' => true, 'email_sent' => true, 'created_at' => '2026-03-15 10:05:00'],
            ['user_id' => 11, 'application_id' => 8, 'message' => 'تهانينا! تم اعتماد بحثك (IRB-2026-008) نهائياً وإصدار شهادة IRB.', 'channel' => 'system', 'is_read' => false, 'email_sent' => true, 'created_at' => '2026-02-01 10:05:00'],
            ['user_id' => 1, 'application_id' => 5, 'message' => 'بحثك (IRB-2026-005) botato chips chips botato سداد رسوم التقديم الأولية.', 'channel' => 'system', 'is_read' => false, 'email_sent' => true, 'created_at' => '2026-04-20 17:00:00'],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}
