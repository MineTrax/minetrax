<?php

namespace App\Listeners;

use App\Events\NewsCommentCreated;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewsCommentedByUserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStaffOnNewsComment implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewsCommentCreated $event): void
    {
        $news = $event->news;

        // If staff notification is disabled for this news, then return.
        if (! $news->is_notify_staff_on_comment) {
            return;
        }

        $roles = Role::where('is_staff', true)->get();
        $staffUsers = User::role($roles->pluck('name'))->get();
        foreach ($staffUsers as $user) {
            $user->notify(new NewsCommentedByUserNotification($event->comment, $event->news, $event->causer));
        }
    }
}
