<?php

namespace App\Notifications;

use App\Models\StoreOrder;
use App\Services\StoreCurrencyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordMessage;

/**
 * Tells the buyer their money is on its way back.
 *
 * Carries the amount actually refunded rather than the order total, because a partial refund is a
 * normal outcome and "you have been refunded $20" on a $5 refund is worse than saying nothing.
 * Also goes to guests via an on-demand notifiable, hence the AnonymousNotifiable branch in via().
 */
class StoreOrderRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StoreOrder $order,
        public int $amountMinor,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return $notifiable->notificationPreferencesFor('store_order_refunded');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your order :number has been refunded', ['number' => $this->number()]))
            ->line(__('We have refunded :amount for order :number.', [
                'amount' => $this->formattedAmount(),
                'number' => $this->number(),
            ]))
            ->line(__('Depending on your bank it can take a few days to appear on your statement.'))
            ->line(__('Anything the order delivered in game may have been removed.'))
            ->action(__('View Order'), route('store.order.result', $this->order->uuid));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'uuid' => $this->order->uuid,
            'number' => $this->number(),
            'currency' => $this->order->currency,
            'amount' => $this->amountMinor,
            'amount_formatted' => $this->formattedAmount(),
        ];
    }

    /**
     * Required, not optional: via() offers `discord` whenever the buyer has not narrowed their
     * preferences, and the Discord channel calls this method without checking it exists.
     */
    public function toDiscord($notifiable)
    {
        return DiscordMessage::create()->embed([
            'title' => __('Your order :number has been refunded', ['number' => $this->number()]),
            'description' => __('We have refunded :amount for order :number.', [
                'amount' => $this->formattedAmount(),
                'number' => $this->number(),
            ]),
            'type' => 'rich',
            'url' => route('store.order.result', $this->order->uuid),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function formattedAmount(): string
    {
        return app(StoreCurrencyService::class)->format($this->amountMinor, $this->order->currency);
    }

    private function number(): string
    {
        return strtoupper(substr($this->order->uuid, 0, 8));
    }
}
