<?php

namespace App\Notifications;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Notifications\Channels\Discord\DiscordMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $webhookUrl;

    public function __construct(public News $news)
    {
        $this->webhookUrl = config('services.discord.news_webhook_url');
    }

    public function via($notifiable)
    {
        return ['discord'];
    }

    // Elimina o comenta este método si no deseas enviar correos electrónicos
    /*
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('[Notification] New News Created'))
            ->line('A new news has been created - ' . $this->news->title)
            ->action('View News', route('admin.news.show', [$this->news->id]))
            ->line('Title: ' . $this->news->title);
    }
    */

    public function toArray($notifiable)
    {
        return [
            'id' => $this->news->id,
            'title' => $this->news->title,
            'slug' => $this->news->slug,
        ];
    }

    public function toDiscord($notifiable = null)
    {
        $discordMessage = (new DiscordMessage())
            ->from(__('News 📰'))
            ->title($this->news->title)
            ->description(__('```🛈 To learn more about this announcement, click on the title of this message.```'))
            ->url(route('news.show', [$this->news->slug]))
            ->timestamp(now())
            ->footer('ID: ' . $this->news->id);

        $messageData = $discordMessage->toArray();

        $response = Http::post($this->webhookUrl, $messageData);

        if ($response->failed()) {
            Log::error('Failed to send Discord message. Status code: ' . $response->status());
            Log::error('Response body: ' . $response->body());
        }
    }
}
