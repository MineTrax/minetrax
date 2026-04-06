<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum CustomFormStatus: string implements HasKeyValueSerialization
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case ARCHIVED = 'archived';
}
