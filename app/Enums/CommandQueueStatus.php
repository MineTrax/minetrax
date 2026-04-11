<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum CommandQueueStatus: string implements HasKeyValueSerialization
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case DEFERRED = 'deferred';
    case COMPLETED = 'completed';
}
