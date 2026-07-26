<?php

namespace App\Notifications;

use App\Models\StoreOrder;
use App\Services\StoreCurrencyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

/**
 * Tells staff a sale happened. Separate from the buyer's receipt because it links into the admin
 * order screen and carries the buyer's identity, neither of which belongs in a customer email.
 */
class StoreOrderPlacedStaffNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public StoreOrder $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->notificationPreferencesFor('store_order_placed');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('[Notification] New store order :number', ['number' => $this->number()]))
            ->line(__(':player purchased :count item(s) for :amount.', [
                'player' => $this->order->player_username,
                'count' => $this->order->items->count(),
                'amount' => $this->formattedTotal(),
            ]))
            ->action(__('View Order'), route('admin.store-order.show', $this->order->uuid));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'uuid' => $this->order->uuid,
            'number' => $this->number(),
            'player_username' => $this->order->player_username,
            'total_formatted' => $this->formattedTotal(),
            'causer' => $this->order->user?->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create()->embed([
            'title' => __('[Notification] New store order :number', ['number' => $this->number()]),
            'description' => __(':player purchased :count item(s) for :amount.', [
                'player' => $this->order->player_username,
                'count' => $this->order->items->count(),
                'amount' => $this->formattedTotal(),
            ]),
            'type' => 'rich',
            'url' => route('admin.store-order.show', $this->order->uuid),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function formattedTotal(): string
    {
        return app(StoreCurrencyService::class)->format((int) $this->order->total, $this->order->currency);
    }

    private function number(): string
    {
        return strtoupper(substr($this->order->uuid, 0, 8));
    }
}
