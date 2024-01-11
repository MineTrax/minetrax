<?php

namespace App\Listeners;

use App\Events\CustomFormSubmissionCreated;
use App\Models\User;
use App\Notifications\CustomFormSubmissionCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStaffOnCustomFormSubmission implements ShouldQueue
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
    public function handle(CustomFormSubmissionCreated $event): void
    {
        $customForm = $event->submission->customForm;

        // If staff notification is disabled for this form, then return.
        if (! $customForm->is_notify_staff_on_submission) {
            return;
        }

        $users = User::permission(['read custom_form_submissions'])->get();
        $roleWeight = $customForm->min_role_weight_to_view_submission;
        foreach ($users as $user) {
            if ($roleWeight && $user->maxRoleWeight() < $roleWeight) {
                continue;
            }
            $user->notify(new CustomFormSubmissionCreatedNotification($event->submission));
        }
    }
}
