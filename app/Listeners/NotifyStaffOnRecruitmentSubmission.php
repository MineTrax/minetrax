<?php

namespace App\Listeners;

use App\Events\RecruitmentSubmissionCreated;
use App\Models\User;
use App\Notifications\RecruitmentSubmissionCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyStaffOnRecruitmentSubmission implements ShouldQueue
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
    public function handle(RecruitmentSubmissionCreated $event): void
    {
        $recruitment = $event->submission->recruitment;

        // If staff notification is disabled for this form, then return.
        if (! $recruitment->is_notify_staff_on_submission) {
            return;
        }

        $users = User::permission(['read recruitment_submissions'])->get();
        $roleWeight = $recruitment->min_role_weight_to_view_submission;
        foreach ($users as $user) {
            if ($roleWeight && $user->maxRoleWeight() < $roleWeight) {
                continue;
            }
            $user->notify(new RecruitmentSubmissionCreatedNotification($event->submission));
        }
    }
}
