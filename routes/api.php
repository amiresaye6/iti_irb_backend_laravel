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
    Route::get('/', [ApplicationController::class, 'index'])->middleware('role:admin,manager');
    Route::post('/', [ApplicationController::class, 'store'])->middleware('role:student');
    Route::get('/pending_admin', [ApplicationController::class, 'get_pending_admin_Apps'])->middleware('role:admin');
    Route::get('/under_review', [ApplicationController::class, 'get_under_review_Apps'])->middleware('role:admin');
    Route::get('/approved_by_reviewer', [ApplicationController::class, 'get_approved_by_reviewer_Apps'])->middleware('role:admin');
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
});

// Documents routes
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