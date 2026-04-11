<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum NewsType: int implements HasKeyValueSerialization
{
    case General = 0;
    case Announcement = 1;
    case Event = 2;
}
