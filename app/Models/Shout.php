<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shout extends BaseModel
{
    use HasFactory;

    public function toArray(): array
    {
        return parent::toArray() + [
                'permissions' => $this->permissions(['delete'])
            ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
