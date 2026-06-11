<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\User;
use App\Models\Log;
use Carbon\Carbon;


class DashboardController extends Controller
{
    // ── GET /api/dashboard/stats ──────────────────────
    public function stats()
    {
        $byStage = Application::query()
            ->selectRaw('current_stage, COUNT(*) as count')
            ->groupBy('current_stage')
            ->pluck('count', 'current_stage')
            ->toArray();

        return response()->json([
            'total_applications'  => Application::count(),
            'pending_users'       => User::where('is_active', false)
                                         ->where('role', 'student')
                                         ->count(),
            'pending_admin'       => $byStage['pending_admin']    ?? 0,
            'under_review'        => $byStage['under_review']     ?? 0,
            'final_review'        => $byStage['final_review']     ?? 0,
            'awaiting_payment'    => $byStage['awaiting_payment'] ?? 0,
            'approved'            => $byStage['approved']         ?? 0,
            'rejected'            => $byStage['rejected']         ?? 0,
            'approved_this_month' => Application::where('current_stage', 'approved')
                                         ->whereMonth('updated_at', Carbon::now()->month)
                                         ->whereYear('updated_at',  Carbon::now()->year)
                                         ->count(),
            'by_stage'            => $byStage,
        ]);
    }

    // ── GET /api/dashboard/logs ───────────────────────
    public function logs()
    {
        $logs = Log::with([
                    'user:id,full_name,role',
                    'application:id,serial_number,title'
                ])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

        return response()->json(['data' => $logs]);
    }

    // ── GET /api/dashboard/applications/recent ────────
    public function recentApplications()
    {
        $apps = Application::with('student:id,full_name')
                ->latest()
                ->limit(10)
                ->get();

        return response()->json(['data' => $apps]);
    }
}