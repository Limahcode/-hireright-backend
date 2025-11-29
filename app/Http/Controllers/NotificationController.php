<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send a notification.
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'channel' => 'nullable|string|in:push,email,sms',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $user = User::findOrFail($data['user_id']);
        $notification = $this->notificationService->sendNotification(
            $user,
            $data['title'],
            $data['message'],
            $data['type'] ?? 'general',
            $data['channel'] ?? 'push'
        );

        return response()->json(['success' => true, 'notification' => $notification]);
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json(['data' => $notifications]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    }
}
