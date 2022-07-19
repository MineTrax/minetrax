<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserYouAreMuted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $causer)
    {
        //
    }

    public function via($notifiable)
    {
        return $notifiable->notificationPreferences()['you_are_muted'] ?? ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.user.you-are-muted', [
            'causer' => $this->causer,
            'name' => $notifiable->name
        ])
            ->subject(__('[Notification] You are muted by a staff member'));
    }

    public function toArray($notifiable)
    {
        return [
            'causer' => $this->causer->only('id', 'name', 'username', 'profile_photo_url')
        ];
    }
}
