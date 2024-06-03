<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CommandQueueStatus extends Enum
{
    const PENDING = 'pending';

    const RUNNING = 'running';

    const FAILED = 'failed';

    const CANCELLED = 'cancelled';

    const DEFERRED = 'deferred';

    const COMPLETED = 'completed';

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
