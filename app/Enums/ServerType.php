<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Paper()
 * @method static static Spigot()
 * @method static static Forge()
 * @method static static Bukkit()
 * @method static static Vanilla()
 * @method static static Bungee()
 */
final class ServerType extends Enum
{
    const Paper = 0;

    const Spigot = 1;

    const Forge = 2;

    const Bukkit = 3;

    const Vanilla = 4;

    const Bungee = 5;

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
