<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\ManagerController;
use App\Http\Controllers\API\CertificateController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// login route
Route::post('/login', [AuthController::class, 'login']);

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
