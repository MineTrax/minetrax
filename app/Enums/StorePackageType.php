<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * What a purchase actually delivers.
 *
 * A Minecraft package runs its command set in-game; a gift card issues a store credit code to the
 * buyer; BOTH does each of them from the one purchase.
 */
enum StorePackageType: string implements HasKeyValueSerialization
{
    case MINECRAFT_PACKAGE = 'minecraft_package';
    case GIFTCARD = 'giftcard';
    case BOTH = 'both';

    public function deliversCommands(): bool
    {
        return $this !== self::GIFTCARD;
    }

    public function issuesGiftCard(): bool
    {
        return $this !== self::MINECRAFT_PACKAGE;
    }
}
