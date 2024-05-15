<?php

namespace App\Models;

class FailedJob extends BaseModel
{
    protected $casts = [
        'payload' => 'array',
        'failed_at' => 'datetime',
    ];
}
