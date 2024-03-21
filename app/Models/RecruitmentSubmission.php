<?php

namespace App\Models;

use App\Enums\RecruitmentSubmissionStatus;
use App\Traits\HasCommentsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecruitmentSubmission extends BaseModel
{
    use HasCommentsTrait, HasFactory;

    protected $casts = [
        'data' => 'array',
        'status' => RecruitmentSubmissionStatus::class,
        'last_act_at' => 'datetime',
    ];

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastActor()
    {
        return $this->belongsTo(User::class, 'last_act_by');
    }
}
