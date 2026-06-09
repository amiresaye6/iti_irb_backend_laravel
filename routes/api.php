<?php
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\LogController;

use App\Http\Controllers\API\ManagerController;
use App\Http\Controllers\API\CertificateController;
use App\Http\Controllers\API\SuperAdminController;

use App\Http\Controllers\API\PaymentController;

use App\Http\Controllers\API\DashboardController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// login route
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register',         [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// applications routes
Route::middleware('auth:sanctum')->prefix('applications')->group(function () {
    Route::get('/', [ApplicationController::class, 'index'])->middleware('role:admin,manager');
    Route::post('/', [ApplicationController::class, 'store'])->middleware('role:student');
    Route::get('/pending_admin', [ApplicationController::class, 'get_pending_admin_Apps'])->middleware('role:admin');
    Route::get('/under_review', [ApplicationController::class, 'get_under_review_Apps'])->middleware('role:admin');
    Route::get('/final_review', [ApplicationController::class, 'get_final_review_Apps'])->middleware('role:admin');
    Route::get('/awaiting_payment', [ApplicationController::class, 'get_awaiting_payment_Apps'])->middleware('role:admin');
    Route::get('/approved', [ApplicationController::class, 'get_approved_Apps'])->middleware('role:admin');
    Route::get('/rejected', [ApplicationController::class, 'get_rejected_Apps'])->middleware('role:admin');
    Route::post('/reject/{id}', [ApplicationController::class, 'rejectApp'])->middleware('role:admin');
    Route::get('/student', [ApplicationController::class, 'getAppsByUserId'])->middleware('role:student');
    Route::get('/student/{id}', [ApplicationController::class, 'getAppsByStudentId'])->middleware('role:admin');
    Route::get('/{id}', [ApplicationController::class, 'show'])->middleware('role:admin,student');
    Route::patch('/{id}', [ApplicationController::class, 'edit'])->middleware('role:student');
    Route::post('/{id}', [ApplicationController::class, 'toNextStage'])->middleware('role:admin,reviewer,manager');
    Route::get('/{id}/Docs', [DocumentController::class, 'getDocsByAppId']);
    Route::post('/{id}/ask-for-modification', [ApplicationController::class, 'askForModification'])->middleware('role:admin,reviewer,manager');
    Route::post('/{id}/ask-for-review', [ApplicationController::class, 'askForReview_afterModifications'])->middleware('role:student');
    Route::get('/{id}/comments', [ApplicationController::class, 'getCommentsByApplicationId']);
});

// Documents routes
Route::middleware('auth:sanctum')->prefix('Documents')->group(function () {
    Route::get('/{id}', [DocumentController::class, 'show']);
    Route::delete('/{id}', [DocumentController::class, 'destroy']);
});

// ─── Payment Routes ─────────────────────────────────────────────
// Paymob webhook (no auth — verified via HMAC)
Route::post('/payments/callback', [PaymentController::class, 'callback']);

// Authenticated payment routes
Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    // Admin/Manager: set fee & dashboard
    Route::post('/set-fee/{applicationId}', [PaymentController::class, 'setFee'])
        ->middleware('role:admin,manager');
    Route::get('/admin', [PaymentController::class, 'adminIndex'])
        ->middleware('role:admin,manager');

    // Student: checkout, pending, history, verify
    Route::post('/checkout/{applicationId}', [PaymentController::class, 'checkout'])
        ->middleware('role:student');
    Route::get('/pending', [PaymentController::class, 'pendingPayments'])
        ->middleware('role:student');
    Route::get('/history', [PaymentController::class, 'history'])
        ->middleware('role:student');
    Route::get('/verify/{clientSecret}', [PaymentController::class, 'verify'])
        ->middleware('role:student');

    // Shared: receipt
    Route::get('/{paymentId}/receipt', [PaymentController::class, 'receipt'])
        ->middleware('role:student,admin,manager');
});

Route::middleware('auth:sanctum')->group(function () {

    

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [UserController::class, 'show']);
    Route::put('/profile', [UserController::class, 'update']);

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/users',              [UserController::class, 'index']);
        Route::get('/users/{id}',         [UserController::class, 'show']);
        Route::delete('/users/{id}',      [UserController::class, 'destroy']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
        Route::post('/admin/add_staff',[AuthController::class, 'register']);
        Route::get('/pending_users', [UserController::class, 'showPendingUsers']);

    });

    // Super Admin only
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/super-admin/users',  [SuperAdminController::class, 'index']);
    });
    // Dashboard — Admin, Manager, Super Admin only
    Route::prefix('dashboard')->middleware('role:admin,manager,super_admin')->group(function () {
    Route::get('/stats',                 [DashboardController::class, 'stats']);
    Route::get('/logs',                  [DashboardController::class, 'logs']);
    Route::get('/applications/recent',   [DashboardController::class, 'recentApplications']);
});

    // ─── REVIEWS ROUTES ───
    
    // Reviewer only
    Route::middleware('role:reviewer')->prefix('reviewer')->group(function () {
        Route::get('/dashboard', [ReviewController::class, 'getDashboardKPIs']);
        Route::get('/pending-researches', [ReviewController::class, 'getPendingResearches']);
        Route::get('/pending-assignments', [ReviewController::class, 'getPendingAssignments']);
        Route::get('/active-assignments', [ReviewController::class, 'getActiveAssignments']);
        Route::get('/awaiting-decision-assignments', [ReviewController::class, 'getAwaitingDecisionAssignments']);
        Route::get('/history', [ReviewController::class, 'getAssignmentHistory']);
        Route::get('/reviews/{applicationId}', [ReviewController::class, 'getReviewDetails']);
        Route::post('/assignments/{reviewId}/accept', [ReviewController::class, 'acceptAssignment']);
        Route::post('/assignments/{reviewId}/refuse', [ReviewController::class, 'refuseAssignment']);
        Route::post('/reviews/{applicationId}/submit', [ReviewController::class, 'submitDecision']);
    });

    // Admin/Manager assigning reviewers
    Route::middleware('role:admin,manager')->prefix('admin/reviews')->group(function () {
        Route::get('/under-review', [ReviewController::class, 'getApplicationsUnderReview']);
        Route::get('/available-reviewers', [ReviewController::class, 'getAvailableReviewers']);
        Route::post('/assign/{applicationId}', [ReviewController::class, 'assignReviewer']);
        Route::get('/assignments/{applicationId}', [ReviewController::class, 'getAssignedReviewers']);
        Route::get('/all', [ReviewController::class, 'getAllSystemReviews']);
    });

    // ─── NOTIFICATIONS ROUTES ───
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);

});

// manager routes

Route::middleware(['auth:sanctum'])->prefix('manager')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard']);
    Route::get('/final-approvals', [ManagerController::class, 'finalApprovals']);
    Route::get('/decisions/{id}', [ManagerController::class, 'decisionDetails']);
    Route::post('/decisions/{id}/process', [ManagerController::class, 'processDecision']);
    Route::get('/reports-statistics', [ManagerController::class, 'reportsStatistics']);
    Route::get('/staff/certificates/{application_id}/download', [CertificateController::class, 'downloadForStaff']);
    Route::get('/certificates/{application_id}', [ManagerController::class, 'getCertificateDetails']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/student/certificates/{application_id}/preview', [CertificateController::class, 'preview']);
});


// logs routes
Route::middleware('auth:sanctum')->prefix('logs')->group(function () {
    Route::get('/', [LogController::class, 'index'])->middleware('role:admin,manager,super_admin');
    Route::get('/application/{app_id}', [LogController::class, 'getLogsByAppId'])->middleware('role:admin,manager,super_admin');
    Route::get('/user/{user_id}', [LogController::class, 'getLogsByUserId'])->middleware('role:admin,manager,super_admin');
    Route::get('/type/{type}', [LogController::class, 'getLogsByType'])->middleware('role:admin,manager,super_admin');
    Route::get('/submission', [LogController::class, 'getSubmissionLogs'])->middleware('role:admin,manager,super_admin');
    Route::get('/assignment', [LogController::class, 'getAssignmentLogs'])->middleware('role:admin,manager,super_admin');
    Route::get('/decision', [LogController::class, 'getDecisionLogs'])->middleware('role:admin,manager,super_admin');
    Route::get('/status-change', [LogController::class, 'getStatusChangeLogs'])->middleware('role:admin,manager,super_admin');
    Route::get('/auth', [LogController::class, 'getAuthLogs'])->middleware('role:admin,manager,super_admin');
    Route::get('/serial-number/{serial_number}', [LogController::class, 'getLogsBySerialNumber'])->middleware('role:admin,manager,super_admin');
});