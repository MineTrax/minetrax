<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PlayerPunishmentType extends Enum
{
    const BAN = 'PLAYER_BAN';
    const IP_BAN = 'IP_BAN';
    const COMPOSITE_BAN = 'COMPOSITE_BAN';
    const PLAYER_MUTE = 'PLAYER_MUTE';
    const IP_MUTE = 'IP_MUTE';
    const COMPOSITE_MUTE = 'COMPOSITE_MUTE';
    const PLAYER_WARN = 'PLAYER_WARN';
    const IP_WARN = 'IP_WARN';
    const COMPOSITE_WARN = 'COMPOSITE_WARN';
    const PLAYER_KICK = 'PLAYER_KICK';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
