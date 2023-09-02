<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class FontType extends Enum
{
    const Nunito = 'Nunito';

    const Ubuntu = 'Ubuntu';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
