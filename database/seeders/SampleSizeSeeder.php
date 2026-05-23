<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SampleSize;

class SampleSizeSeeder extends Seeder
{
    public function run(): void
    {
        $sampleSizes = [
            ['application_id' => 1, 'sampler_id' => 6, 'calculated_size' => 150, 'sample_amount' => 350.00, 'notes' => 'تم حساب العينة بناء على معدل الانتشار السنوي لمرض السكري وتم إضافة 10% لتجنب التسرب', 'created_at' => '2026-03-03 09:00:00'],
            ['application_id' => 2, 'sampler_id' => 7, 'calculated_size' => 500, 'sample_amount' => 800.00, 'notes' => 'حجم العينة ممثل لمدارس المرحلة الابتدائية بعدة محافظات في الدلتا', 'created_at' => '2026-04-12 10:15:00'],
            ['application_id' => 4, 'sampler_id' => 6, 'calculated_size' => 300, 'sample_amount' => 400.00, 'notes' => 'الحد الأدنى المطلوب لتحقيق دلالة إحصائية في هذه المقارنة', 'created_at' => '2026-02-23 11:30:00'],
            ['application_id' => 7, 'sampler_id' => 6, 'calculated_size' => 60, 'sample_amount' => 300.00, 'notes' => 'حجم العينة كافٍ للدراسة المقطعية مع مراعاة الندرة النسبية', 'created_at' => '2026-04-22 14:00:00'],
            ['application_id' => 8, 'sampler_id' => 7, 'calculated_size' => 1000, 'sample_amount' => 1200.00, 'notes' => 'حجم العينة كبير نسبياً لتغطية الاختلافات الجغرافية في ريف المحافظة', 'created_at' => '2026-01-20 09:45:00'],
            ['application_id' => 9, 'sampler_id' => 6, 'calculated_size' => 120, 'sample_amount' => 400.00, 'notes' => 'تم الحساب بناء على قوة إحصائية 80% ومستوى ثقة 95%', 'created_at' => '2026-04-19 13:20:00'],
            ['application_id' => 12, 'sampler_id' => 6, 'calculated_size' => 200, 'sample_amount' => 400.00, 'notes' => 'عينة ممثلة للطلاب', 'created_at' => '2026-04-21 09:00:00'],
            ['application_id' => 13, 'sampler_id' => 7, 'calculated_size' => 150, 'sample_amount' => 300.00, 'notes' => 'تم احتساب الحجم المطلوب', 'created_at' => '2026-04-22 09:00:00'],
            ['application_id' => 14, 'sampler_id' => 6, 'calculated_size' => 180, 'sample_amount' => 350.00, 'notes' => 'حجم مناسب', 'created_at' => '2026-04-23 09:00:00'],
            ['application_id' => 15, 'sampler_id' => 7, 'calculated_size' => 300, 'sample_amount' => 500.00, 'notes' => 'تمت الموافقة على الحجم', 'created_at' => '2026-04-23 10:00:00'],
        ];

        foreach ($sampleSizes as $sample) {
            SampleSize::create($sample);
        }
    }
}
