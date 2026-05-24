<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ApplicationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// login route
Route::post('/login', [AuthController::class, 'login']);

// applications routes
Route::middleware('auth:sanctum')->prefix('applications')->group(function () {
    Route::get('/', [ApplicationController::class, 'index']);
    Route::post('/', [ApplicationController::class, 'store']);
    Route::get('/student', [ApplicationController::class, 'getAppsByUserId']);
    Route::get('/student/{id}', [ApplicationController::class, 'getAppsByStudentId']);
    Route::get('/{id}', [ApplicationController::class, 'show']);


});