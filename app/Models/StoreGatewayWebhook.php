<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreGatewayWebhook extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
