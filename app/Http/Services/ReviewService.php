<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Notification;
use App\Models\Review;
use App\Models\ReviewComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Dashboard KPIs for a specific reviewer
     */
    public function getKPIs($reviewerId)
    {
        $kpis = [
            'totalAssigned' => 0,
            'awaitingAcceptance' => 0,
            'pendingAction' => 0,
            'needsModification' => 0,
            'completed' => 0,
        ];

        $kpis['totalAssigned'] = Review::where('reviewer_id', $reviewerId)->count();
        
        $kpis['awaitingAcceptance'] = Review::where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'awaiting_acceptance')
            ->count();

        $kpis['pendingAction'] = Review::where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->where('decision', 'pending')
            ->count();

        $kpis['needsModification'] = Review::where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->where('decision', 'needs_modification')
            ->count();

        $kpis['completed'] = Review::where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->whereIn('decision', ['approved', 'rejected'])
            ->count();

        return $kpis;
    }

    /**
     * Decision Distribution for Dashboard Charts
     */
    public function getDecisionsDistribution($reviewerId)
    {
        $stats = Review::where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->select('decision', DB::raw('count(id) as count'))
            ->groupBy('decision')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $decisionMap = [
            'pending' => ['label' => 'قيد الانتظار', 'color' => '#3498db'],
            'approved' => ['label' => 'موافقة', 'color' => '#27ae60'],
            'needs_modification' => ['label' => 'طلب تعديل', 'color' => '#f39c12'],
            'rejected' => ['label' => 'رفض', 'color' => '#e74c3c'],
        ];

        foreach ($stats as $stat) {
            $dec = $stat->decision;
            if (isset($decisionMap[$dec])) {
                $labels[] = $decisionMap[$dec]['label'];
                $colors[] = $decisionMap[$dec]['color'];
                $data[] = $stat->count;
            }
        }

        return ['labels' => $labels, 'data' => $data, 'colors' => $colors];
    }

    /**
     * Monthly Reviews for Dashboard Charts
     */
    public function getMonthlyReviews($reviewerId)
    {
        // Get counts by month
        $stats = Review::where('reviewer_id', $reviewerId)
            ->whereNotNull('reviewed_at')
            ->where('assignment_status', 'accepted')
            ->select(DB::raw("DATE_FORMAT(reviewed_at, '%Y-%m') as month"), DB::raw('count(id) as count'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->limit(6)
            ->get();

        return [
            'labels' => $stats->pluck('month'),
            'data' => $stats->pluck('count'),
        ];
    }

    /**
     * Reviewer's Pending Researches (Pending or Needs Modification, Under Review stage)
     */
    public function getPendingResearches($reviewerId)
    {
        $reviews = Review::with(['application', 'application.student'])
            ->where('reviewer_id', $reviewerId)
            ->whereIn('decision', ['pending', 'needs_modification'])
            ->whereHas('application', function ($q) {
                $q->where('current_stage', 'under_review');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->formatAssignments($reviews);
    }

    /**
     * Assignments awaiting reviewer acceptance.
     */
    public function getPendingAssignments($reviewerId)
    {
        $reviews = Review::with(['application', 'application.student', 'assigner'])
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'awaiting_acceptance')
            ->orderBy('assigned_at', 'desc')
            ->get();

        return $this->formatAssignments($reviews);
    }

    /**
     * Reviewer's ACCEPTED assignments — their active work queue.
     */
    public function getReviewerAssignments($reviewerId)
    {
        $reviews = Review::with(['application', 'application.student'])
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->whereIn('decision', ['pending' , 'needs_modification'])
            ->orderBy('assigned_at', 'desc')
            ->get();

        return $this->formatAssignments($reviews);
    }

    /**
     * Reviewer Assignment History (all)
     */
    public function getAssignmentHistory($reviewerId)
    {
        $reviews = Review::with(['application', 'application.student', 'assigner'])
            ->where('reviewer_id', $reviewerId)
            ->orderBy('assigned_at', 'desc')
            ->get();

        return $this->formatAssignments($reviews);
    }

    /**
     * Get details for a specific review
     */
    public function getReview($applicationId, $reviewerId)
    {
        $review = Review::with(['application.student', 'application.documents', 'comments'])
            ->where('application_id', $applicationId)
            ->where('reviewer_id', $reviewerId)
            ->first();

        if ($review && $review->application->is_blinded) {
            $review->application->principal_investigator = "معلومات محجوبة";
            $review->application->co_investigators = null;
        }

        return $review;
    }

    /**
     * Accept Assignment
     */
    public function acceptAssignment($reviewId, $reviewerId)
    {
        $review = Review::where('id', $reviewId)
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'awaiting_acceptance')
            ->first();

        if ($review) {
            $review->update(['assignment_status' => 'accepted']);
            return true;
        }
        return false;
    }

    /**
     * Refuse Assignment
     */
    public function refuseAssignment($reviewId, $reviewerId, $reason)
    {
        $review = Review::where('id', $reviewId)
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'awaiting_acceptance')
            ->first();

        if ($review) {
            $review->update([
                'assignment_status' => 'refused',
                'refusal_reason' => $reason
            ]);
            return true;
        }
        return false;
    }

    /**
     * Submit Review Decision
     */
    public function submitReviewDecision($applicationId, $reviewerId, $decision, $comment)
    {
        $app = Application::find($applicationId);
        if (!$app) {
            return ['success' => false, 'message' => 'البحث غير موجود'];
        }
        if ($app->current_stage !== 'under_review') {
            return ['success' => false, 'message' => 'لا يمكن مراجعة هذا البحث إلا في مرحلة المراجعة'];
        }

        $validDecisions = ['approved', 'needs_modification', 'rejected'];
        if (!in_array($decision, $validDecisions)) {
            return ['success' => false, 'message' => 'قرار غير صالح'];
        }

        if ($decision !== 'approved' && empty(trim($comment))) {
            return ['success' => false, 'message' => 'يجب إضافة تعليقات عند الرفض أو طلب التعديل'];
        }

        $review = Review::where('application_id', $applicationId)
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->first();

        if (!$review) {
            return ['success' => false, 'message' => 'لم يتم العثور على المراجعة أو لم يتم قبول الإسناد بعد'];
        }

        // Save Decision
        $review->update([
            'decision' => $decision,
            'reviewed_at' => now()
        ]);

        // Save Comment
        if (!empty(trim($comment))) {
            ReviewComment::create([
                'review_id' => $review->id,
                'comment' => $comment
            ]);
        }

        // ── FINAL DECISION (APPROVED / REJECTED) ──────────────────────────
        if (in_array($decision, ['approved', 'rejected'])) {
            $app->update(['current_stage' => 'final_review']);
            
            // Notify student if rejected
            if ($decision === 'rejected') {
                Notification::create([
                    'user_id' => $app->student_id,
                    'application_id' => $app->id,
                    'message' => "تم رفض بحثك رقم ({$app->serial_number}) من قبل المراجع. يرجى مراجعة ملاحظات المراجعة.",
                    'channel' => 'system'
                ]);
            }

            // Notify Managers
            $managers = User::where('role', 'manager')->get();
            $statusText = $decision === 'approved' ? 'بالموافقة على' : 'برفض';
            $message = "تم اتخاذ قرار {$statusText} البحث رقم ({$app->serial_number}) من قبل المراجع ويحتاج لاعتمادك النهائي.";
            
            foreach ($managers as $mgr) {
                Notification::create([
                    'user_id' => $mgr->id,
                    'application_id' => $app->id,
                    'message' => $message,
                    'channel' => 'system'
                ]);
            }
        }

        return ['success' => true, 'message' => 'تم حفظ القرار بنجاح'];
    }

    // ── ADMIN FUNCTIONS ───────────────────────────────────────────────

    public function getApplicationsUnderReview()
    {
        $applications = Application::with(['student'])
            ->where('current_stage', 'under_review')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($applications as $app) {
            $activeReview = Review::with('reviewer')
                ->where('application_id', $app->id)
                ->whereIn('assignment_status', ['awaiting_acceptance', 'accepted'])
                ->orderBy('assigned_at', 'desc')
                ->first();
                
            if ($activeReview) {
                $app->active_assignment = [
                    'assignment_status' => $activeReview->assignment_status,
                    'full_name' => $activeReview->reviewer ? $activeReview->reviewer->full_name : 'مجهول'
                ];
            } else {
                $app->active_assignment = null;
            }
        }
        
        return $applications;
    }

    public function getAvailableReviewers($applicationId = null)
    {
        $query = User::where('role', 'reviewer')->where('is_active', true);

        if ($applicationId) {
            $assignedReviewerIds = Review::where('application_id', $applicationId)
                ->whereIn('assignment_status', ['awaiting_acceptance', 'accepted'])
                ->pluck('reviewer_id')
                ->toArray();
                
            if (!empty($assignedReviewerIds)) {
                $query->whereNotIn('id', $assignedReviewerIds);
            }
        }

        return $query->get();
    }

    public function getAssignedReviewers($applicationId)
    {
        $reviews = Review::with('reviewer')->where('application_id', $applicationId)->orderBy('assigned_at', 'desc')->get();
        return $reviews->map(function ($review) {
            return [
                'review_id'         => $review->id,
                'reviewer_id'       => $review->reviewer_id,
                'reviewer_name'     => $review->reviewer ? $review->reviewer->full_name : '—',
                'assignment_status' => $review->assignment_status,
                'decision'          => $review->decision,
                'assigned_at'       => $review->assigned_at,
                'refusal_reason'    => $review->refusal_reason,
            ];
        });
    }

    public function assignReviewer($applicationId, $reviewerId, $adminId)
    {
        // Check for active assignments to this specific reviewer for this app
        $exists = Review::where('application_id', $applicationId)
            ->where('reviewer_id', $reviewerId)
            ->whereIn('assignment_status', ['awaiting_acceptance', 'accepted'])
            ->exists();

        if ($exists) {
            return false;
        }

        Review::create([
            'application_id' => $applicationId,
            'reviewer_id' => $reviewerId,
            'assigned_by' => $adminId,
            'assignment_status' => 'awaiting_acceptance',
            'decision' => 'pending'
        ]);

        $app = Application::find($applicationId);
        if ($app) {
            Notification::create([
                'user_id' => $reviewerId,
                'application_id' => $applicationId,
                'message' => "تم تكليفك بمراجعة بحث جديد برقم ({$app->serial_number}). يرجى قبول أو رفض الإسناد.",
                'channel' => 'system'
            ]);
        }

        return true;
    }

    public function getAllSystemReviews()
    {
        return Review::with(['application.student', 'reviewer', 'comments'])
            ->orderBy('assigned_at', 'desc')
            ->get();
    }

    // ── HELPERS ────────────────────────────────────────────────────────

    private function formatAssignments($reviews)
    {
        $formatted = [];
        foreach ($reviews as $review) {
            $app = $review->application;
            if (!$app) continue;

            $pi = $app->principal_investigator;
            if ($app->is_blinded) {
                $pi = "معلومات محجوبة";
            }

            $formatted[] = [
                'review_id' => $review->id,
                'application_id' => $app->id,
                'serial_number' => $app->serial_number,
                'title' => $app->title,
                'principal_investigator' => $pi,
                'is_blinded' => $app->is_blinded,
                'created_at' => $app->created_at,
                'department' => $app->student ? $app->student->department : null,
                'decision' => $review->decision,
                'assignment_status' => $review->assignment_status,
                'reviewed_at' => $review->reviewed_at,
                'assigned_at' => $review->assigned_at,
                'current_stage' => $app->current_stage,
                'assigned_by_name' => $review->assigner ? $review->assigner->full_name : null,
                'refusal_reason' => $review->refusal_reason,
            ];
        }
        return $formatted;
    }

    /**
     * Reviewer's assignments awaiting decision.
     */
    public function getAwaitingDecisionAssignments($reviewerId)
    {
        $reviews = Review::with(['application', 'application.student'])
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->whereNotIn('decision', ['approved', 'rejected'])
            ->whereHas('application', function ($q) {
                $q->where('current_stage', 'under_review');
            })
            ->orderBy('assigned_at', 'desc')
            ->get();

        return $this->formatAssignments($reviews);
    }
}
