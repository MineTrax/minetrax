<?php

namespace App\Listeners;

use App\Enums\RecruitmentSubmissionStatus;
use App\Events\RecruitmentSubmissionStatusChanged;
use App\Models\User;
use App\Notifications\RecruitmentSubmissionStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUsersOnRecruitmentSubmissionStatusChanged implements ShouldQueue
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
    public function handle(RecruitmentSubmissionStatusChanged $event): void
    {
        $recruitment = $event->submission->recruitment;

        if ($event->submission->status == RecruitmentSubmissionStatus::WITHDRAWN) { // Notify Staff
            if (! $recruitment->is_notify_staff_on_submission) {
                return;
            }

            $users = User::permission(['read recruitment_submissions'])->get();
            $roleWeight = $recruitment->min_role_weight_to_view_submission;
            foreach ($users as $user) {
                if ($roleWeight && $user->maxRoleWeight() < $roleWeight) {
                    continue;
                }
                $user->notify(
                    new RecruitmentSubmissionStatusChangedNotification($event->submission, $event->causer, $event->previousStatus)
                );
            }
        } else { // Notify Applicant!
            $event->submission->user->notify(
                new RecruitmentSubmissionStatusChangedNotification($event->submission, $event->causer, $event->previousStatus)
            );
        }
    }
}
