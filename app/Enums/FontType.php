<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class FontType extends Enum
{
    const NUNITO = 'Nunito';

    const UBUNTU = 'Ubuntu';

    const INTER = 'Inter';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
