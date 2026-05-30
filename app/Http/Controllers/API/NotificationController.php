<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $notifications = Notification::with('application')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Get a specific notification
     */
    public function show($id)
    {
        $notification = Notification::with('application')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // Mark as read when opened
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->json($notification);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'تم تحديد الكل كمقروء']);
    }
}
