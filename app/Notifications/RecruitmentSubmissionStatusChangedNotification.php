<?php

namespace App\Notifications;

use App\Enums\RecruitmentSubmissionStatus;
use App\Models\RecruitmentSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

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
        return $notifiable->notificationPreferencesFor('recruitment_submission_status_changed');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->submission->user->name.' (@'.$this->submission->user->username.')';
        [$title, $body, $route] = $this->generateTitleBodyRoute($applicant);

        return (new MailMessage)
            ->subject($title)
            ->line($body)
            ->action('View Request', $route)
            ->line('Applicant: '.$applicant)
            ->line('Status: '.ucfirst($this->submission->status->value))
            ->lineIf($this->submission->last_act_reason, 'Reason: '.$this->submission->last_act_reason)
            ->line('Application: '.$this->submission->recruitment->title);
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

    public function toDiscord($notifiable)
    {
        $applicant = $this->submission->user->name.' (@'.$this->submission->user->username.')';
        [$title, $body, $route, $causer] = $this->generateTitleBodyRoute($applicant);

        return DiscordMessage::create()->embed([
            'title' => $title,
            'description' => $body,
            'type' => 'rich',
            'url' => $route,
            'fields' => [
                [
                    'name' => 'Applicant',
                    'value' => $applicant,
                    'inline' => true,
                ],
                [
                    'name' => 'Status',
                    'value' => ucfirst($this->submission->status->value),
                    'inline' => true,
                ],
                [
                    'name' => 'Application',
                    'value' => $this->submission->recruitment->title,
                    'inline' => false,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => $causer,
            ],
        ]);
    }

    private function generateTitleBodyRoute($applicant): array
    {
        $title = $body = $route = $causer = '';
        if ($this->submission->status == RecruitmentSubmissionStatus::WITHDRAWN) {
            $title = __('[Notification] An application request withdrawn by user.');
            $body = __(':applicant has withdrawn his application request for :recruitment.', [
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
                    $title = __('[Notification] Your Application request status has changed.');
                    $body = __(':causer has started processing your application request for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
                case RecruitmentSubmissionStatus::ONHOLD:
                    $title = __('[Notification] Your Application request status has changed.');
                    $body = __(':causer has put on-hold your application request for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
                case RecruitmentSubmissionStatus::APPROVED:
                    $title = __('[Notification] Congratulations! Your Application request has been Approved.');
                    $body = __(':causer has approved your application request for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
                case RecruitmentSubmissionStatus::REJECTED:
                    $title = __('[Notification] Sorry! Your Application request has been Rejected.');
                    $body = __(':causer has rejected your application request for :recruitment.', [
                        'causer' => $causer,
                        'recruitment' => $this->submission->recruitment->title,
                    ]);
                    break;
            }
        }

        return [$title, $body, $route, $causer];
    }
}
