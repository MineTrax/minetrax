<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static LIGHT_BLUE()
 * @method static static BLUE()
 * @method static static RED()
 * @method static static ORANGE()
 * @method static static LIME()
 * @method static static GREEN()
 * @method static static TEAL()
 * @method static static INDIGO()
 * @method static static FUCHSIA()
 */
final class ThemeType extends Enum
{
    const LIGHT_BLUE = 'light-blue';

    const BLUE = 'blue';

    const RED = 'red';

    const ORANGE = 'orange';

    const LIME = 'lime';

    const GREEN = 'green';

    const TEAL = 'teal';

    const INDIGO = 'indigo';

    const FUCHSIA = 'fuchsia';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
