<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CustomFormStatus extends Enum
{
    const DRAFT = 'draft';

    const ACTIVE = 'active';

    const DISABLED = 'disabled';

    const ARCHIVED = 'archived';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
