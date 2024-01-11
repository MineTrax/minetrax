<?php

namespace App\Notifications;

use App\Models\CustomFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomFormSubmissionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public CustomFormSubmission $submission)
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $submissionBy = $this->submission->user ? $this->submission->user->name : 'Anonymous User';
        return (new MailMessage)
            ->subject(__('[Notification] New submission received for a custom form'))
            ->line('A new submission has been received for custom form - ' . $this->submission->customForm->title)
            ->action('View Submission', route('admin.custom-form-submission.show', [$this->submission->id]))
            ->line('Submission by: ' . $submissionBy);
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
            'form' => $this->submission->customForm->only('id', 'title', 'slug'),
            'causer' => $this->submission?->user?->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }
}
