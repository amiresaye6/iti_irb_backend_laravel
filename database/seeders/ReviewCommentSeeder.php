<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewComment;

class ReviewCommentSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            ['review_id' => 1, 'comment' => 'منهجية البحث ممتازة، ولا يوجد مانع أخلاقي من التطبيق.', 'created_at' => '2026-03-10 10:00:00'],
            ['review_id' => 2, 'comment' => 'موافق. أهداف الدراسة واضحة وإقرار المرضى مستوفي الشروط.', 'created_at' => '2026-03-11 14:00:00'],
            ['review_id' => 3, 'comment' => 'يجب توضيح كيفية حماية بيانات الأطفال المشاركين في الدراسة بدقة أكبر في نموذج الموافقة المستنيرة.', 'created_at' => '2026-04-18 09:00:00'],
            ['review_id' => 3, 'comment' => 'أيضاً يُرجى مراجعة صياغة نموذج الموافقة المستنيرة ليكون أكثر وضوحاً لأولياء الأمور.', 'created_at' => '2026-04-18 10:30:00'],
            ['review_id' => 4, 'comment' => 'يوجد تضارب مصالح واضح مع الشركة المصنعة للمضاد الحيوي لم يتم الإفصاح عنه بشكل كافٍ في النماذج.', 'created_at' => '2026-02-28 12:00:00'],
            ['review_id' => 5, 'comment' => 'البروتوكول ممتاز ولا توجد أي ملاحظات أخلاقية.', 'created_at' => '2026-01-25 10:00:00'],
            ['review_id' => 6, 'comment' => 'استمارات الموافقة المستنيرة مكتوبة بلغة بسيطة ومناسبة.', 'created_at' => '2026-01-26 12:00:00'],
            ['review_id' => 7, 'comment' => 'مراجعة أولية مقبولة. البروتوكول واضح والمنهجية سليمة.', 'created_at' => '2026-04-23 09:00:00'],
            ['review_id' => 8, 'comment' => 'لا يوجد موانع أخلاقية. العينة مناسبة.', 'created_at' => '2026-04-23 10:00:00'],
            ['review_id' => 9, 'comment' => 'موافق. البحث مستوفٍ لجميع الشروط.', 'created_at' => '2026-04-24 09:00:00'],
        ];

        foreach ($comments as $comment) {
            ReviewComment::create($comment);
        }
    }
}
