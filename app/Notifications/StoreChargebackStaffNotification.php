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
 * Tells staff a dispute was lost.
 *
 * Goes only to staff. A chargeback is raised at the buyer's bank, so the buyer already knows; what
 * they do not know is that their perks have been taken back, and staff need to see it because
 * disputes carry a fee and often repeat.
 */
class StoreChargebackStaffNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StoreOrder $order,
        public bool $wasBanned = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->notificationPreferencesFor('store_chargeback_received');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('[Notification] Chargeback on order :number', ['number' => $this->number()]))
            ->line($this->summary());

        if ($this->wasBanned) {
            $mail->line(__('A store ban has been raised automatically for this buyer.'));
        }

        return $mail->action(__('View Order'), route('admin.store.order.show', $this->order->uuid));
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
            'was_banned' => $this->wasBanned,
            'causer' => $this->order->user?->only('id', 'name', 'username', 'profile_photo_url'),
        ];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create()->embed([
            'title' => __('[Notification] Chargeback on order :number', ['number' => $this->number()]),
            'description' => $this->summary(),
            'type' => 'rich',
            'url' => route('admin.store.order.show', $this->order->uuid),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function summary(): string
    {
        return __(':player disputed :amount and the funds have been reversed. Their grants have been revoked.', [
            'player' => $this->order->player_username,
            'amount' => $this->formattedTotal(),
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
