<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            ['application_id' => 1, 'reviewer_id' => 6, 'assigned_by' => 5, 'assigned_at' => '2026-03-08 09:00:00', 'assignment_status' => 'accepted', 'decision' => 'approved','review_document' => 'uploads/reviews/IRB-ReviewerCHECKLIST.doc'  ,'refusal_reason' => null, 'reviewed_at' => '2026-03-10 10:00:00'],
            ['application_id' => 2, 'reviewer_id' => 6, 'assigned_by' => 5, 'assigned_at' => '2026-05-02 10:00:00', 'assignment_status' => 'awaiting_acceptance', 'decision' => 'pending', 'refusal_reason' => null, 'reviewed_at' => null],
            ['application_id' => 4, 'reviewer_id' => 7, 'assigned_by' => 5, 'assigned_at' => '2026-02-26 11:00:00', 'assignment_status' => 'accepted', 'decision' => 'rejected','review_document' => 'uploads/reviews/IRB-ReviewerCHECKLIST.doc' , 'refusal_reason' => null, 'reviewed_at' => '2026-02-28 12:00:00'],
            ['application_id' => 8, 'reviewer_id' => 6, 'assigned_by' => 5, 'assigned_at' => '2026-01-23 09:00:00', 'assignment_status' => 'accepted', 'decision' => 'approved','review_document' => 'uploads/reviews/IRB-ReviewerCHECKLIST.doc', 'refusal_reason' => null, 'reviewed_at' => '2026-01-25 10:00:00'],
            ['application_id' => 9, 'reviewer_id' => 8, 'assigned_by' => 5, 'assigned_at' => '2026-05-01 14:30:00', 'assignment_status' => 'refused', 'decision' => 'pending', 'refusal_reason' => 'اعتذار لضيق الوقت وضغط العمل الحالي', 'reviewed_at' => null],
            ['application_id' => 12, 'reviewer_id' => 7, 'assigned_by' => 5, 'assigned_at' => '2026-04-20 09:00:00', 'assignment_status' => 'timed_out', 'decision' => 'pending', 'refusal_reason' => null, 'reviewed_at' => null],
            ['application_id' => 13, 'reviewer_id' => 8, 'assigned_by' => 5, 'assigned_at' => '2026-05-03 11:00:00', 'assignment_status' => 'awaiting_acceptance', 'decision' => 'pending', 'refusal_reason' => null, 'reviewed_at' => null],
            ['application_id' => 14, 'reviewer_id' => 6, 'assigned_by' => 5, 'assigned_at' => '2026-05-02 09:00:00', 'assignment_status' => 'accepted', 'decision' => 'pending', 'refusal_reason' => null, 'reviewed_at' => null],
            ['application_id' => 15, 'reviewer_id' => 8, 'assigned_by' => 5, 'assigned_at' => '2026-04-22 08:30:00', 'assignment_status' => 'accepted', 'decision' => 'approved','review_document' => 'uploads/reviews/IRB-ReviewerCHECKLIST.doc' , 'refusal_reason' => null, 'reviewed_at' => '2026-04-24 09:30:00'],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
