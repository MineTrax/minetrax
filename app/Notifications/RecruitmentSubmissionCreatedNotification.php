<?php

namespace App\Notifications;

use App\Models\RecruitmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

class RecruitmentSubmissionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public RecruitmentSubmission $submission)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->notificationPreferencesFor('recruitment_submission_created');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->submission->user->name.' (@'.$this->submission->user->username.')';

        return (new MailMessage)
            ->subject(__('[Notification] New recruitment application received.'))
            ->line('A new application has been received for recruitment - '.$this->submission->recruitment->title)
            ->action('View Application', route('admin.recruitment-submission.show', [$this->submission->id]))
            ->line('Applicant: '.$applicant)
            ->line('Recruitment: '.$this->submission->recruitment->title);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->submission->id,
            'recruitment' => $this->submission->recruitment->only('id', 'title', 'slug'),
            'applicant' => $this->submission?->user?->only('id', 'name', 'username', 'profile_photo_url'),
            'causer' => $this->submission?->user?->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        $applicant = $this->submission->user->name.' (@'.$this->submission->user->username.')';

        return DiscordMessage::create()->embed([
            'title' => __('[Notification] New recruitment application received.'),
            'description' => 'A new application has been received for recruitment - '.$this->submission->recruitment->title,
            'type' => 'rich',
            'url' => route('admin.recruitment-submission.show', [$this->submission->id]),
            'fields' => [
                [
                    'name' => 'Applicant',
                    'value' => $applicant,
                    'inline' => true,
                ],
                [
                    'name' => 'Recruitment',
                    'value' => $this->submission->recruitment->title,
                    'inline' => false,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => $applicant,
            ],
        ]);
    }
}
