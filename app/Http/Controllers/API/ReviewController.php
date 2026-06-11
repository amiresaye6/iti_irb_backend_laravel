<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    private $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    // ─── REVIEWER ENDPOINTS ───

    public function getDashboardKPIs()
    {
        $userId = Auth::id();
        return response()->json([
            'kpis' => $this->reviewService->getKPIs($userId),
            'distribution' => $this->reviewService->getDecisionsDistribution($userId),
            'monthly' => $this->reviewService->getMonthlyReviews($userId)
        ]);
    }

    public function getPendingResearches()
    {
        return response()->json(
            $this->reviewService->getPendingResearches(Auth::id())
        );
    }

    public function getPendingAssignments()
    {
        return response()->json(
            $this->reviewService->getPendingAssignments(Auth::id())
        );
    }

    public function getActiveAssignments()
    {
        return response()->json(
            $this->reviewService->getReviewerAssignments(Auth::id())
        );
    }

    public function getAssignmentHistory()
    {
        return response()->json(
            $this->reviewService->getAssignmentHistory(Auth::id())
        );
    }

    public function acceptAssignment(Request $request, $reviewId)
    {
        $success = $this->reviewService->acceptAssignment($reviewId, Auth::id());
        if ($success) {
            return response()->json(['message' => 'تم قبول الإسناد بنجاح']);
        }
        return response()->json(['message' => 'حدث خطأ أو الإسناد غير متاح'], 400);
    }

    public function refuseAssignment(Request $request, $reviewId)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);
        
        $success = $this->reviewService->refuseAssignment($reviewId, Auth::id(), $request->reason);
        if ($success) {
            return response()->json(['message' => 'تم رفض الإسناد']);
        }
        return response()->json(['message' => 'حدث خطأ أو الإسناد غير متاح'], 400);
    }

    public function submitDecision(Request $request, $applicationId)
    {
        $request->validate([
            'decision' => 'required|in:approved,needs_modification,rejected',
            'comment' => 'nullable|string' ,
        ]);

        if($request->decision !== 'needs_modification'){
            $request->validate([
                'review_document' => 'required|file|mimes:pdf,doc,docx|max:4096',
            ]);
        }

        $result = $this->reviewService->submitReviewDecision(
            $applicationId, 
            Auth::id(), 
            $request->decision, 
            $request->comment ?? '',
            $request->file('review_document') ?? null,
        );

        if ($result['success']) {
            return response()->json(['message' => $result['message']]);
        }
        return response()->json(['message' => $result['message']], 400);
    }

    public function getReviewDetails($applicationId)
    {
        $review = $this->reviewService->getReview($applicationId, Auth::id());
        if (!$review) {
            return response()->json(['message' => 'المراجعة غير موجودة'], 404);
        }
        return response()->json($review);
    }

    // ─── ADMIN ENDPOINTS ───

    public function getApplicationsUnderReview()
    {
        return response()->json($this->reviewService->getApplicationsUnderReview());
    }

    public function getAvailableReviewers(\Illuminate\Http\Request $request)
    {
        return response()->json($this->reviewService->getAvailableReviewers($request->query('applicationId')));
    }

    public function getAssignedReviewers($applicationId)
    {
        return response()->json($this->reviewService->getAssignedReviewers($applicationId));
    }

    public function assignReviewer(Request $request, $applicationId)
    {
        $request->validate([
            'reviewer_id' => 'required|exists:users,id'
        ]);

        $success = $this->reviewService->assignReviewer($applicationId, $request->reviewer_id, Auth::id());
        
        if ($success) {
            return response()->json(['message' => 'تم إسناد البحث للمراجع بنجاح']);
        }
        return response()->json(['message' => 'هذا المراجع مسند إليه هذا البحث مسبقاً'], 400);
    }

    public function getAllSystemReviews()
    {
        return response()->json($this->reviewService->getAllSystemReviews());
    }

    public function getAwaitingDecisionAssignments()
    {
        return response()->json(
            $this->reviewService->getAwaitingDecisionAssignments(Auth::id())
        );
    }
}
