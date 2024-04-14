<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

class PostLikedByUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $postId, public $causer)
    {
        //
    }

    public function via($notifiable)
    {
        return $notifiable->notificationPreferencesFor('like_on_post');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.post.liked-by-user', [
            'causer' => $this->causer,
            'postId' => $this->postId,
            'name' => $notifiable->name,
        ])
            ->subject(__('[Notification] Someone liked your post'));
    }

    public function toArray($notifiable)
    {
        return [
            'post_id' => $this->postId,
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        $causer = $this->causer->name;

        return DiscordMessage::create()->embed([
            'title' => __('[Notification] :user liked your post', [
                'user' => $causer,
            ]),
            'type' => 'rich',
            'url' => route('post.show', $this->postId),
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => $causer,
            ],
        ]);
    }
}
