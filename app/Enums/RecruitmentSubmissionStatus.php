<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RecruitmentSubmissionStatus extends Enum
{
    const PENDING = 'pending';

    const INPROGRESS = 'inprogress';

    const APPROVED = 'approved';

    const REJECTED = 'rejected';

    const WITHDRAWN = 'withdrawn';

    const ONHOLD = 'onhold';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
