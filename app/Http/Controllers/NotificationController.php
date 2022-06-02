<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->cursorPaginate();

        if ($request->wantsJson()) {
            return $notifications;
        }

        return Inertia::render('Profile/UserNotifications', [
            'notificationsList' => $notifications,
        ]);
    }

    public function postMarkAsRead(Request $request)
    {
        $request->validate([
            'notifications' => 'sometimes|array'
        ]);
        $notificationIdArray = $request->get('notifications');
        if ($notificationIdArray) {
            foreach ($notificationIdArray as $notificationId) {
                $notification = $request->user()->notifications()->firstOrFail($notificationId);
                $notification?->markAsRead();
            }
        } else {
            $request->user()->unreadNotifications->markAsRead();
        }

        return redirect()->back();
    }
}
