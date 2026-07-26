<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePaymentGateway: string implements HasKeyValueSerialization
{
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case MANUAL = 'manual';
    case GIFTCARD = 'giftcard';
    case FREE = 'free';
}
