<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StoreGiftCardTransactionType: string implements HasKeyValueSerialization
{
    case ISSUE = 'issue';
    case REDEEM = 'redeem';
    case REVERSAL = 'reversal';
    case ADJUSTMENT = 'adjustment';
}
