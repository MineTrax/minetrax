<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PollOption extends BaseModel
{
    use HasFactory;

    public function poll(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function voters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'poll_votes')->withTimestamps();
    }
}
