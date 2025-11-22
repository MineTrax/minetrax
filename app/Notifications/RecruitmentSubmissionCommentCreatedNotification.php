<?php

namespace App\Notifications;

use App\Enums\CommentType;
use App\Models\Comment;
use App\Models\RecruitmentSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\RateLimiter;
use NotificationChannels\Discord\DiscordMessage;
const NOTIFICATION_DELAY = 60;

class RecruitmentSubmissionCommentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Comment $comment, public RecruitmentSubmission $submission, public User $causer)
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
        $channels = $notifiable->notificationPreferencesFor('recruitment_submission_comment_created');

        if (empty($channels)) {
            return [];
        }

        $key = 'notification_recruitment_submission_comment_created::' . $notifiable->id . '::' . $this->submission->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return [];
        }

        RateLimiter::hit($key, NOTIFICATION_DELAY);

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->submission->user->name . ' (@' . $this->submission->user->username . ')';
        [$title, $body, $route] = $this->generateTitleBodyRoute();

        return (new MailMessage)
            ->subject($title)
            ->line($body)
            ->line('"' . $this->comment->comment . '"')
            ->action('View Request', $route)
            ->line('Applicant: ' . $applicant)
            ->line('Status: ' . ucfirst($this->submission->status->value))
            ->line('Application: ' . $this->submission->recruitment->title);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $forStaff = false;
        if ($this->comment->type == CommentType::RECRUITMENT_APPLICANT_MESSAGE) {
            $forStaff = true;
        }

        return [
            'comment_id' => $this->comment->id,
            'submission_id' => $this->submission->id,
            'recruitment' => $this->submission->recruitment->only('id', 'title', 'slug'),
            'comment_type' => $this->comment->type,
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url'),
            'applicant' => $this->submission?->user?->only('id', 'name', 'username', 'profile_photo_url'),
            'for_staff' => $forStaff,
        ];
    }

    public function toDiscord($notifiable)
    {
        $applicant = $this->submission->user->name . ' (@' . $this->submission->user->username . ')';
        [$title, $body, $route, $causer] = $this->generateTitleBodyRoute();

        return DiscordMessage::create()->embed([
            'title' => $title,
            'description' => $body,
            'type' => 'rich',
            'url' => $route,
            'fields' => [
                [
                    'name' => 'Message',
                    'value' => $this->comment->comment,
                    'inline' => false,
                ],
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

    private function generateTitleBodyRoute(): array
    {
        $title = $body = $route = '';
        $causer = $this->causer->name . ' (@' . $this->causer->username . ')';
        if ($this->comment->type == CommentType::RECRUITMENT_APPLICANT_MESSAGE) {
            // Sent to staffs
            $title = __('[Notification] New message received on a application request.');
            $body = __(':causer sent new message on his application request for :recruitment:', [
                'causer' => $causer,
                'recruitment' => $this->submission->recruitment->title,
            ]);
            $route = route('admin.recruitment-submission.show', [$this->submission->id]);
        } elseif ($this->comment->type == CommentType::RECRUITMENT_STAFF_MESSAGE) {
            // Sent to applicant
            $title = __('[Notification] Your application request has a new message.');
            $body = __(':causer sent you a message on your application request for :recruitment:', [
                'causer' => $causer,
                'recruitment' => $this->submission->recruitment->title,
            ]);
            $route = route('recruitment-submission.show', [
                'recruitment' => $this->submission->recruitment->slug,
                'submission' => $this->submission->id,
            ]);
        }

        return [$title, $body, $route, $causer];
    }
}
