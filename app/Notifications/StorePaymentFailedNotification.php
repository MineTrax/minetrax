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
 * Tells the buyer a charge did not go through, and that the order is still theirs to pay.
 *
 * The gateway's own failure reason is deliberately not repeated: it is written for developers, is
 * occasionally blunt about fraud checks, and there is nothing the buyer can do with it.
 */
class StorePaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public StoreOrder $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return $notifiable->notificationPreferencesFor('store_payment_failed');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Payment for order :number did not go through', ['number' => $this->number()]))
            ->line(__('Your payment of :amount for order :number was declined, so nothing has been charged.', [
                'amount' => app(StoreCurrencyService::class)->format((int) $this->order->amount_due, $this->order->currency),
                'number' => $this->number(),
            ]))
            ->line(__('The order is still waiting for you and can be paid again with another method.'))
            ->action(__('Try Again'), route('store.order.result', $this->order->uuid));
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
            'amount_due' => (int) $this->order->amount_due,
        ];
    }

    /**
     * Required, not optional: via() offers `discord` whenever the buyer has not narrowed their
     * preferences, and the Discord channel calls this method without checking it exists.
     */
    public function toDiscord($notifiable)
    {
        return DiscordMessage::create()->embed([
            'title' => __('Payment for order :number did not go through', ['number' => $this->number()]),
            'description' => __('Nothing has been charged. The order is still waiting for you and can be paid again with another method.'),
            'type' => 'rich',
            'url' => route('store.order.result', $this->order->uuid),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function number(): string
    {
        return strtoupper(substr($this->order->uuid, 0, 8));
    }
}
