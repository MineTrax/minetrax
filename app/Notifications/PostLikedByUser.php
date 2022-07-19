<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostLikedByUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $postId, public $causer)
    {
        //
    }

    public function via($notifiable)
    {
        return $notifiable->notificationPreferences()['like_on_post'] ?? ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.post.liked-by-user', [
            'causer' => $this->causer,
            'postId' => $this->postId,
            'name' => $notifiable->name
        ])
            ->subject(__('[Notification] Someone liked your post'));
    }

    public function toArray($notifiable)
    {
        return [
            'post_id' => $this->postId,
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url')
        ];
    }
}
