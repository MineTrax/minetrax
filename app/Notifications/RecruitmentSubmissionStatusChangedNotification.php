<?php

namespace App\Notifications;

use App\Enums\RecruitmentSubmissionStatus;
use App\Models\RecruitmentSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecruitmentSubmissionStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public RecruitmentSubmission $submission,
        public User $causer,
        public RecruitmentSubmissionStatus $previousStatus
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->submission->user->name.' (@'.$this->submission->user->username.')';

        if ($this->submission->status == RecruitmentSubmissionStatus::WITHDRAWN) {
            $title = __('[Notification] An application withdrawn by user.');
            $body = __(':applicant has withdrawn his application for :recruitment.', [
                'applicant' => $applicant,
                'recruitment' => $this->submission->recruitment->title,
            ]);
            $route = route('admin.recruitment-submission.show', [$this->submission->id]);
        } else {
            $route = route('recruitment-submission.show', [
                'recruitment' => $this->submission->recruitment->slug,
                'submission' => $this->submission->id,
            ]);

            $causer = $this->causer->name.' (@'.$this->causer->username.')';
            switch ($this->submission->status) {
                case RecruitmentSubmissionStatus::INPROGRESS:
                    $title = __('[Notification] Your Application status has changed.');
                    $body = __(':causer has started processing your application for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
                case RecruitmentSubmissionStatus::ONHOLD:
                    $title = __('[Notification] Your Application status has changed.');
                    $body = __(':causer has put on-hold your application for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
                case RecruitmentSubmissionStatus::APPROVED:
                    $title = __('[Notification] Congratulations! Your Application has been Approved.');
                    $body = __(':causer has approved your application for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
                case RecruitmentSubmissionStatus::REJECTED:
                    $title = __('[Notification] Sorry! Your Application has been Rejected.');
                    $body = __(':causer has rejected your application for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
            }
        }

        return (new MailMessage)
            ->subject($title)
            ->line($body)
            ->action('View Application', $route)
            ->line('Applicant: '.$applicant)
            ->line('Status: '.ucfirst($this->submission->status->value))
            ->lineIf($this->submission->last_act_reason, 'Reason: '.$this->submission->last_act_reason)
            ->line('Recruitment: '.$this->submission->recruitment->title);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $forStaff = $this->submission->status == RecruitmentSubmissionStatus::WITHDRAWN ? true : false;

        return [
            'id' => $this->submission->id,
            'status' => $this->submission->status,
            'previous_status' => $this->previousStatus,
            'last_act_reason' => $this->submission->last_act_reason,
            'recruitment' => $this->submission->recruitment->only('id', 'title', 'slug'),
            'applicant' => $this->submission?->user?->only('id', 'name', 'username', 'profile_photo_url'),
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url'),
            'for_staff' => $forStaff,
        ];
    }
}
