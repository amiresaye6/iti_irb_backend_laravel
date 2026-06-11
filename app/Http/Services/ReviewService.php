<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Notification;
use App\Models\Review;
use App\Models\ReviewComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;


class ReviewService
{


     /**
     * Returns the public URL for the IRB reviewer checklist template.
     * Used by the API so the frontend can offer a "Download Template" button.
     */
    public function getChecklistTemplateUrl(): string
    {
        return asset('storage/uploads/reviews/IRB-ReviewerCHECKLIST.doc');
    }

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

        $review->checklist_template_url =$this->getChecklistTemplateUrl();
        $review->review_document_url = $review->review_document
        ? asset('storage/' . $review->review_document)
        : null;
        
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

   
    

     public function submitReviewDecision(
        int $applicationId,
        int $reviewerId,
        string $decision,
        string $comment = '',
        ?UploadedFile $reviewDocument = null
    ): array {
        // ── Validate application ──────────────────────────────────────────────
        $app = Application::find($applicationId);
        if (!$app) {
            return ['success' => false, 'message' => 'البحث غير موجود'];
        }
        if ($app->current_stage !== 'under_review') {
            return ['success' => false, 'message' => 'لا يمكن مراجعة هذا البحث إلا في مرحلة المراجعة'];
        }
 
        // ── Validate decision value ───────────────────────────────────────────
        $validDecisions = ['approved', 'needs_modification', 'rejected'];
        if (!in_array($decision, $validDecisions)) {
            return ['success' => false, 'message' => 'قرار غير صالح'];
        }
 
        // ── Comment required for non-approval decisions ───────────────────────
        if ($decision !== 'approved' && empty(trim($comment))) {
            return ['success' => false, 'message' => 'يجب إضافة تعليقات عند الرفض أو طلب التعديل'];
        }
 
        // ── Checklist document required for final decisions ───────────────────
        if (in_array($decision, ['approved', 'rejected']) && !$reviewDocument) {
            return ['success' => false, 'message' => 'يجب إرفاق قائمة تدقيق المراجعة المعبأة عند الموافقة أو الرفض'];
        }
 
        // ── Find the accepted review record ───────────────────────────────────
        $review = Review::where('application_id', $applicationId)
            ->where('reviewer_id', $reviewerId)
            ->where('assignment_status', 'accepted')
            ->first();
 
        if (!$review) {
            return ['success' => false, 'message' => 'لم يتم العثور على المراجعة أو لم يتم قبول الإسناد بعد'];
        }
 
        // ── Store the uploaded checklist document ─────────────────────────────
        $reviewDocumentPath = null;
        if ($reviewDocument) {
            // Build a clean student name slug (works for Arabic or Latin names)
            $studentName = $app->student?->full_name ?? $app->principal_investigator ?? 'reviewer';
            $studentSlug = Str::slug($studentName, '_') ?: 'student';
            if (empty($studentSlug)) {
                // Fallback for purely Arabic names that Str::slug empties
                $studentSlug = 'student_' . $app->student_id;
            }
 
            $extension  = $reviewDocument->getClientOriginalExtension() ?: 'pdf';
            $uniqueId   = uniqid('', true); // e.g. 64f3a1b2c3d4e.5678
            $filename   = "{$studentSlug}_review{$review->id}_{$uniqueId}.{$extension}";
 
            // Directory: uploads/reviews/{review_id}/
            $directory  = "uploads/reviews/{$review->id}";
 
            // Store inside storage/app/public/ so the symlink exposes it
            $reviewDocumentPath = $reviewDocument->storeAs($directory, $filename, 'public');
        }
 
        // ── Persist decision ──────────────────────────────────────────────────
        $updateData = [
            'decision'    => $decision,
            'reviewed_at' => now(),
        ];
        if ($reviewDocumentPath) {
            $updateData['review_document'] = $reviewDocumentPath;
        }
        $review->update($updateData);
 
        // ── Persist comment ───────────────────────────────────────────────────
        if (!empty(trim($comment))) {
            ReviewComment::create([
                'review_id' => $review->id,
                'comment'   => $comment,
            ]);
        }
 
        // ── Handle final decisions (approved / rejected) ──────────────────────
        if (in_array($decision, ['approved', 'rejected'])) {
            $app->update(['current_stage' => 'final_review']);
 
            if ($decision === 'rejected') {
                Notification::create([
                    'user_id'        => $app->student_id,
                    'application_id' => $app->id,
                    'message'        => "تم رفض بحثك رقم ({$app->serial_number}) من قبل المراجع. يرجى مراجعة ملاحظات المراجعة.",
                    'channel'        => 'system',
                ]);
            }
 
            $managers   = User::where('role', 'manager')->get();
            $statusText = $decision === 'approved' ? 'بالموافقة على' : 'برفض';
            $message    = "تم اتخاذ قرار {$statusText} البحث رقم ({$app->serial_number}) من قبل المراجع ويحتاج لاعتمادك النهائي.";
 
            foreach ($managers as $mgr) {
                Notification::create([
                    'user_id'        => $mgr->id,
                    'application_id' => $app->id,
                    'message'        => $message,
                    'channel'        => 'system',
                ]);
            }
        } elseif ($decision === 'rejected') {
            // Permanently reject the application
            $app->update(['current_stage' => 'rejected']);
            
            // Notify student
            Notification::create([
                'user_id' => $app->student_id,
                'application_id' => $app->id,
                'message' => "تم رفض بحثك رقم ({$app->serial_number}) من قبل المراجع. يرجى مراجعة ملاحظات المراجعة.",
                'channel' => 'system'
            ]);
        }
 
        return ['success' => true, 'message' => 'تم حفظ القرار بنجاح'];
    }





    // ── ADMIN FUNCTIONS ───────────────────────────────────────────────

    public function getApplicationsUnderReview()
    {
        $applications = Application::with(['student'])
            ->whereIn('current_stage', ['pending_admin', 'under_review']) 
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
            if ($app->current_stage === 'pending_admin') {
                $app->update(['current_stage' => 'under_review']);
            }

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
