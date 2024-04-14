<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

class UserYouAreMuted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $causer)
    {
        //
    }

    public function via($notifiable)
    {
        return $notifiable->notificationPreferencesFor('you_are_muted');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.user.you-are-muted', [
            'causer' => $this->causer,
            'name' => $notifiable->name,
        ])
            ->subject(__('[Notification] You are Muted by :user', [
                'user' => $this->causer->name,
            ]));
    }

    public function toArray($notifiable)
    {
        return [
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        $causer = $this->causer->name;

        return DiscordMessage::create()->embed([
            'title' => __('[Notification] You are Muted by :user', [
                'user' => $causer,
            ]),
            'url' => route('home'),
            'description' => __('Oh no! You are muted by a staff member and can no longer send messages or chat in our website. If you think this was a mistake please create an appeal.'),
            'type' => 'rich',
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => __('Muted by: :user', [
                    'user' => $causer,
                ]),
            ],
        ]);
    }
}
