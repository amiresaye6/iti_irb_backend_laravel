<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        return response()->json([
            'status' => true,
            'data'   => $users
        ], 200);
    }


    public function show(Request $request): JsonResponse
    {
    return response()->json([
        'status' => true,
        'data'   => $request->user(),
    ]);
    }
    public function activate($id): JsonResponse
    {
        $user = $this->userService->activateUser($id);
        return response()->json([
            'status'  => true,
            'message' => 'تم تفعيل حساب المستخدم بنجاح، ويمكنه الآن تسجيل الدخول.',
            'data'    => $user
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $this->userService->deleteUser($id);
        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المستخدم وجميع ملفاته المرفوعة بنجاح.'
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name'    => ['sometimes', 'string', 'min:10'],
            'phone_number' => ['sometimes', 'string'],
            'faculty'      => ['sometimes', 'string'],
            'department'   => ['sometimes', 'string'],
        ]);

        $updatedUser = $this->userService->updateProfile($request->user(), $validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات البروفايل بنجاح.',
            'data'    => $updatedUser
        ], 200);
    }
    
}
