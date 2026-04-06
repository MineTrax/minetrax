<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum PlayerPunishmentType: string implements HasKeyValueSerialization
{
    case BAN = 'ban';
    case MUTE = 'mute';
    case WARN = 'warn';
    case KICK = 'kick';
}
