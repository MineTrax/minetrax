<?php

namespace App\Events;

use App\Models\StorePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A charge attempt was rejected. The order is untouched and stays PENDING, so this says "that card
 * did not work", never "the order is dead".
 */
class StorePaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public StorePayment $payment,
        public string $reason,
    ) {}
}
