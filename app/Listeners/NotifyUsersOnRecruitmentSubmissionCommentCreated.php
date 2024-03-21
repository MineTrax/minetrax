<?php

namespace App\Listeners;

use App\Enums\CommentType;
use App\Events\RecruitmentSubmissionCommentCreated;
use App\Models\User;
use App\Notifications\RecruitmentSubmissionCommentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUsersOnRecruitmentSubmissionCommentCreated implements ShouldQueue
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
    public function handle(RecruitmentSubmissionCommentCreated $event): void
    {
        $comment = $event->comment;
        $recruitment = $event->submission->recruitment;

        if ($comment->type == CommentType::RECRUITMENT_APPLICANT_MESSAGE) { // Notify Staff
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
                    new RecruitmentSubmissionCommentCreatedNotification($comment, $event->submission, $event->causer)
                );
            }
        } else if ($comment->type == CommentType::RECRUITMENT_STAFF_MESSAGE) { // Notify Applicant!
            $event->submission->user->notify(
                new RecruitmentSubmissionCommentCreatedNotification($comment, $event->submission, $event->causer)
            );
        }
    }
}
