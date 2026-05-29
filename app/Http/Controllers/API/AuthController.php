<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Log;


class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
   
    $result = $this->authService->login(
        $request->only('email', 'password')
    );
    $userId = $result['user']->id;
    Log::create([
    'user_id' => $userId,
    'type'    => 'auth', 
    'action'  => "المستخدم [{$userId}] قام بتسجيل الدخول من IP: {$request->ip()}"
    ]);
    return response()->json([
        'status'  => true,
        'token'   => $result['token'],
        'message' => 'Login Successful',
        'user'    => $result['user'],
    ]);
    }
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
       $this->authService->logout($user);
       
       Log::create([
       'user_id' => $user->id,
       'type'    => 'auth',
       'action'  => "المستخدم [{$user->id}] قام بتسجيل الخروج من IP: {$request->ip()}"
     ]);

        return response()->json([
            'message' => 'Logout Successful',
            'status'  => true,
            
        ],200);
    }
    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = array_merge($request->validated(), [
            'id_front' => $request->file('id_front'),
            'id_back'  => $request->file('id_back'),
        ]);
        $user = $this->authService->register($userData);
        return response()->json([
            'status' => true,
            'message' => $message = ($user->role === 'student') ? 'تم تسجيل الحساب بنجاح، سيتم تفعيل حسابك من قبل الإدارة قريباً.' : 'تم تسجيل الحساب بنجاح.',
            'data'    => $user

        ],201);
    }
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $sent = $this->authService->sendPasswordResetLink($request->email);

        if (!$sent) {
            return response()->json([
                'status'  => false,
                'message' => 'تعذر إرسال الرابط، تأكد من صحة البريد الإلكتروني.'
            ], 400);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني بنجاح.'
        ], 200);
    }
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], 
        ]);

        $reset = $this->authService->resetPassword($request->only(
            'email', 'password', 'password_confirmation', 'token'
        ));

        if (!$reset) {
            return response()->json([
                'status'  => false,
                'message' => 'كود إعادة التعيين غير صالح أو انتهت صلاحيته.'
            ], 400);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول باستخدام البيانات الجديدة.'
        ], 200);
    }
}

