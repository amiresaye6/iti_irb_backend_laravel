<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applications = [
            ['student_id' => 1, 'serial_number' => 'IRB-2026-001', 'title' => 'تأثير الأدوية الحديثة على مرضى السكري من النوع الثاني المتقدم', 'principal_investigator' => 'د. عمر الفاروق', 'current_stage' => 'approved', 'is_blinded' => true, 'created_at' => '2026-03-01 10:00:00'],
            ['student_id' => 2, 'serial_number' => 'IRB-2026-002', 'title' => 'معدلات انتشار السمنة المفرطة بين طلاب المدارس الابتدائية في الدلتا', 'principal_investigator' => 'د. ليلى عثمان', 'current_stage' => 'under_review', 'is_blinded' => true, 'created_at' => '2026-04-10 11:30:00'],
            ['student_id' => 3, 'serial_number' => 'IRB-2026-003', 'title' => 'مقارنة بين تقنيات التخدير الموضعي والكلي في جراحات الفتق بالمنظار', 'principal_investigator' => 'د. كريم محسن', 'current_stage' => 'awaiting_payment', 'is_blinded' => true, 'created_at' => '2026-04-15 09:15:00'],
            ['student_id' => 4, 'serial_number' => 'IRB-2026-004', 'title' => 'تقييم فعالية المضادات الحيوية واسعة المجال في التهابات الجهاز التنفسي', 'principal_investigator' => 'د. نهى عبد الرحمن', 'current_stage' => 'rejected', 'is_blinded' => true, 'created_at' => '2026-02-20 14:00:00'],
            ['student_id' => 1, 'serial_number' => 'IRB-2026-005', 'title' => 'استخدام الذكاء الاصطناعي في التشخيص المبكر لاعتلال الشبكية السكري', 'principal_investigator' => 'د. عمر الفاروق', 'current_stage' => 'awaiting_payment', 'is_blinded' => true, 'created_at' => '2026-04-20 16:45:00'],
            ['student_id' => 2, 'serial_number' => 'IRB-2026-006', 'title' => 'مدى استجابة الأطفال الخدج لبروتوكولات التغذية الوريدية الحديثة', 'principal_investigator' => 'د. ليلى عثمان', 'current_stage' => 'pending_admin', 'is_blinded' => true, 'created_at' => '2026-04-21 08:00:00'],
            ['student_id' => 10, 'serial_number' => 'IRB-2026-007', 'title' => 'أثر العلاج الطبيعي المكثف بعد جراحات استبدال مفصل الركبة', 'principal_investigator' => 'د. يوسف الشناوي', 'current_stage' => 'awaiting_payment', 'is_blinded' => true, 'created_at' => '2026-04-22 09:00:00'],
            ['student_id' => 11, 'serial_number' => 'IRB-2026-008', 'title' => 'مدى انتشار تسوس الأسنان لدى الأطفال في المناطق الريفية', 'principal_investigator' => 'د. سلمى رضا', 'current_stage' => 'approved', 'is_blinded' => true, 'created_at' => '2026-01-15 10:30:00'],
            ['student_id' => 12, 'serial_number' => 'IRB-2026-009', 'title' => 'تأثير برامج التثقيف الصحي على جودة الحياة لمرضى الفشل الكلوي', 'principal_investigator' => 'د. ماجد توفيق', 'current_stage' => 'under_review', 'is_blinded' => true, 'created_at' => '2026-04-18 11:15:00'],
            ['student_id' => 13, 'serial_number' => 'IRB-2026-010', 'title' => 'مضاعفات جراحات المياه البيضاء وعلاقتها بالأمراض المزمنة', 'principal_investigator' => 'د. سارة كمال', 'current_stage' => 'awaiting_payment', 'is_blinded' => true, 'created_at' => '2026-04-23 08:30:00'],
            ['student_id' => 14, 'serial_number' => 'IRB-2026-011', 'title' => 'فاعلية البروتوكولات المستحدثة في علاج جرثومة المعدة', 'principal_investigator' => 'د. أحمد مصطفى', 'current_stage' => 'pending_admin', 'is_blinded' => true, 'created_at' => '2026-04-23 12:00:00'],
            ['student_id' => 12, 'serial_number' => 'IRB-2026-012', 'title' => 'تأثير الإدارة الذاتية لمرضى الربو على تقليل نوبات الاختناق', 'principal_investigator' => 'د. ماجد توفيق', 'current_stage' => 'final_review', 'is_blinded' => true, 'created_at' => '2026-04-20 11:00:00'],
            ['student_id' => 13, 'serial_number' => 'IRB-2026-013', 'title' => 'مضاعفات ارتفاع ضغط الدم وتأثيره على شبكية العين', 'principal_investigator' => 'د. سارة كمال', 'current_stage' => 'final_review', 'is_blinded' => true, 'created_at' => '2026-04-21 12:00:00'],
            ['student_id' => 1, 'serial_number' => 'IRB-2026-014', 'title' => 'مقارنة تأثير التدخل الجراحي المبكر والتأخير في حالات الكسور المضاعفة', 'principal_investigator' => 'د. عمر الفاروق', 'current_stage' => 'under_review', 'is_blinded' => true, 'created_at' => '2026-04-22 09:00:00'],
            ['student_id' => 2, 'serial_number' => 'IRB-2026-015', 'title' => 'تأثير السهر الطويل على كفاءة الأداء الدراسي لدى المراهقين', 'principal_investigator' => 'د. ليلى عثمان', 'current_stage' => 'approved', 'is_blinded' => true, 'created_at' => '2026-04-23 10:00:00'],
        ];

        foreach ($applications as $app) {
            Application::create($app);
        }
    }
}
