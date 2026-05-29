<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;

class SuperAdminController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(): JsonResponse
   {
    return response()->json([
        'message' => 'success',
        'status' => true,
        'data'   => $this->userService->getAllUsers(),


    ]);
   }
}
