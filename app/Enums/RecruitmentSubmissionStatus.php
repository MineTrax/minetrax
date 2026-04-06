<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum RecruitmentSubmissionStatus: string implements HasKeyValueSerialization
{
    case PENDING = 'pending';
    case INPROGRESS = 'inprogress';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case ONHOLD = 'onhold';
}
