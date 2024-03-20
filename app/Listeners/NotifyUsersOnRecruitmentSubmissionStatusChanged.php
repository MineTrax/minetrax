<?php

namespace App\Listeners;

use App\Enums\RecruitmentSubmissionStatus;
use App\Events\RecruitmentSubmissionStatusChanged;

class NotifyUsersOnRecruitmentSubmissionStatusChanged
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

        // Notify staff if status is changed to `withdrawn`. (is its done by applicant)
        if ($event->submission->status === RecruitmentSubmissionStatus::WITHDRAWN) {
            // Notify all concerned staff.
        } else {
            // Notify the applicant if approved,rejected,onhold,inprogress.
        }
    }
}
