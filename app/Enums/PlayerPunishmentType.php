<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PlayerPunishmentType extends Enum
{
    const BAN = 'ban';
    const MUTE = 'mute';
    const WARN = 'warn';
    const KICK = 'kick';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
