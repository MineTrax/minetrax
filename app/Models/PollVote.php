<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PollVote extends BaseModel
{
    use HasFactory;

    public function option(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PollOption::class);
    }

    public function voter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
