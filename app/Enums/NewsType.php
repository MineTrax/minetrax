<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static General()
 * @method static static Announcement()
 * @method static static Event()
 */
final class NewsType extends Enum
{
    const General = 0;

    const Announcement = 1;

    const Event = 2;

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
