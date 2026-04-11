<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum ServerType: int implements HasKeyValueSerialization
{
    case Paper = 0;
    case Spigot = 1;
    case Forge = 2;
    case Bukkit = 3;
    case Vanilla = 4;
    case Bungee = 5;
}
