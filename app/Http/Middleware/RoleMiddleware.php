<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بالدخول',
            ], 401);
        }

        if ($request->user()->role === 'super_admin') {
            return $next($request);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'status'  => false,
                'message' => 'ليس لديك صلاحية للوصول لهذا المورد',
            ], 403);
        }

        return $next($request);
    }
    
}
