<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

class NewsCommentedByUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Comment $comment, public News $news, public $causer)
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
        return $notifiable->notificationPreferencesFor('news_commented_by_user');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('[Notification] Someone commented on a News'))
            ->line($this->causer->name.' commented on news - '.$this->news->title)
            ->action('View Comment', route('news.show', [$this->news->slug]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'news' => $this->news->only('id', 'slug'),
            'comment_id' => $this->comment->id,
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        $commentor = $this->causer->name;

        return DiscordMessage::create()->embed([
            'title' => __('[Notification] :user commented on a News', [
                'user' => $commentor,
            ]),
            'description' => $this->comment->comment,
            'type' => 'rich',
            'url' => route('news.show', $this->news->slug),
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => $commentor,
            ],
        ]);
    }
}
