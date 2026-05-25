<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        
        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken,
            'message' => 'Login Successful',
        ]);
    }
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Successful',
            
        ],200);
    }
    public function forgotPassword(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Forgot Password method structure is ready'
        ], 200);
    }
    public function register(RegisterRequest $request): JsonResponse
    {

        return response()->json([
            'status' => true,
            'message' => 'Registration Successful',
        ],201);
    }
}
