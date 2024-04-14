<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

class PostCommentedByUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $postId, public Comment $comment, public $causer)
    {
        //
    }

    public function via($notifiable)
    {
        return $notifiable->notificationPreferencesFor('comment_on_post');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.post.commented-by-user', [
            'causer' => $this->causer,
            'postId' => $this->postId,
            'name' => $notifiable->name,
        ])
            ->subject(__('[Notification] Someone commented on your post'));
    }

    public function toArray($notifiable)
    {
        return [
            'post_id' => $this->postId,
            'comment_id' => $this->comment->id,
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        $commentor = $this->causer->name;

        return DiscordMessage::create()->embed([
            'title' => __('[Notification] :user commented on your post', [
                'user' => $commentor,
            ]),
            'description' => $this->comment->comment,
            'type' => 'rich',
            'url' => route('post.show', $this->postId),
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => $commentor,
            ],
        ]);
    }
}
