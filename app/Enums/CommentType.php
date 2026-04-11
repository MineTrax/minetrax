<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum CommentType: string implements HasKeyValueSerialization
{
    case RECRUITMENT_APPLICANT_MESSAGE = 'recruitment_applicant_message';
    case RECRUITMENT_STAFF_MESSAGE = 'recruitment_staff_message';
    case RECRUITMENT_STAFF_WHISPER = 'recruitment_staff_whisper';
    case RECRUITMENT_ACTION = 'recruitment_action';
}
