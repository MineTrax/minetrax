<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * What an arriving referral code does when the visitor is already carrying one.
 *
 * The mode belongs to the code that is *arriving*, not the one already stored — it describes how
 * that code behaves when it turns up second.
 */
enum StoreReferralAttributionMode: string implements HasKeyValueSerialization
{
    /** Whoever sent them first keeps the credit. */
    case FIRST_TOUCH = 'first_touch';

    /** Whoever sent them most recently takes the credit. */
    case LAST_TOUCH = 'last_touch';

    /** The stored code keeps the credit, but its clock starts again. */
    case EXTEND_WINDOW = 'extend_window';
}
