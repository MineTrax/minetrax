<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CommentType extends Enum
{
    const DEFAULT = null;

    const RECRUITMENT_APPLICANT_MESSAGE = 'recruitment_applicant_message';
    const RECRUITMENT_STAFF_MESSAGE = 'recruitment_staff_message';

    const RECRUITMENT_STAFF_WHISPER = 'recruitment_staff_whisper';

    const RECRUITMENT_ACTION = 'recruitment_action';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
