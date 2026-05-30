<?php
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\DocumentController;

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
    Route::get('/', [ApplicationController::class, 'index']);
    Route::post('/', [ApplicationController::class, 'store']);
    Route::get('/pending_admin', [ApplicationController::class, 'get_pending_admin_Apps']);
    Route::get('/under_review', [ApplicationController::class, 'get_under_review_Apps']);
    Route::get('/approved_by_reviewer', [ApplicationController::class, 'get_approved_by_reviewer_Apps']);
    Route::get('/awaiting_payment', [ApplicationController::class, 'get_awaiting_payment_Apps']);
    Route::get('/approved', [ApplicationController::class, 'get_approved_Apps']);
    Route::get('/rejected', [ApplicationController::class, 'get_rejected_Apps']);
    Route::post('/reject/{id}', [ApplicationController::class, 'rejectApp']);
    Route::get('/student', [ApplicationController::class, 'getAppsByUserId']);
    Route::get('/student/{id}', [ApplicationController::class, 'getAppsByStudentId']);
    Route::get('/{id}', [ApplicationController::class, 'show']);
    Route::patch('/{id}', [ApplicationController::class, 'edit']);
    Route::post('/{id}', [ApplicationController::class, 'toNextStage']);
    Route::get('/{id}/Docs', [DocumentController::class, 'getDocsByAppId']);
});

Route::middleware('auth:sanctum')->prefix('Documents')->group(function () {
    Route::get('/{id}', [DocumentController::class, 'show']);
    Route::delete('/{id}', [DocumentController::class, 'destroy']);
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


});