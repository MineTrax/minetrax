<?php

namespace App\Notifications;

use App\Models\StoreOrder;
use App\Services\StoreCurrencyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The buyer's receipt.
 *
 * Also goes to guests, who have no account and therefore no preferences, via an on-demand
 * notifiable — hence the AnonymousNotifiable branch in via().
 */
class StoreOrderPaidNotification extends Notification implements ShouldQueue
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

        return $notifiable->notificationPreferencesFor('store_order_paid');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $currencies = app(StoreCurrencyService::class);

        $mail = (new MailMessage)
            ->subject(__('Your order :number is confirmed', ['number' => $this->number()]))
            ->greeting(__('Thanks for your purchase!'))
            ->line(__('Your payment has been received and your items are on their way to :player in game.', [
                'player' => $this->order->player_username,
            ]));

        foreach ($this->order->items as $item) {
            $mail->line('• '.$item->quantity.' × '.$item->package_name);
        }

        return $mail
            ->line(__('Total: :amount', [
                'amount' => $currencies->format((int) $this->order->total, $this->order->currency),
            ]))
            ->action(__('View Order'), route('store.order.result', $this->order->uuid))
            ->line(__('If you are not online, your items will be delivered the moment you next join the server.'));
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
            'currency' => $this->order->currency,
            'total' => (int) $this->order->total,
            'total_formatted' => app(StoreCurrencyService::class)
                ->format((int) $this->order->total, $this->order->currency),
        ];
    }

    private function number(): string
    {
        return strtoupper(substr($this->order->uuid, 0, 8));
    }
}
