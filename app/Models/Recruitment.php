<?php

namespace App\Models;

use App\Enums\RecruitmentFormStatus;
use App\Enums\RecruitmentSubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class Recruitment extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array',
        'post_approve_actions' => 'array',
        'status' => RecruitmentFormStatus::class,
        'is_allow_only_player_linked_users' => 'boolean',
        'is_allow_only_verified_users' => 'boolean',
        'is_allow_messages_from_users' => 'boolean',
        'is_notify_staff_on_submission' => 'boolean',
    ];

    public function getDescriptionHtmlAttribute(): ?string
    {
        $converter = new GithubFlavoredMarkdownConverter();

        return $this->description ? $converter->convertToHtml($this->description) : null;
    }

    public function submissions()
    {
        return $this->hasMany(RecruitmentSubmission::class);
    }

    public function openSubmissions()
    {
        return $this->hasMany(RecruitmentSubmission::class)->whereIn('status', [
            RecruitmentSubmissionStatus::PENDING,
            RecruitmentSubmissionStatus::INPROGRESS,
            RecruitmentSubmissionStatus::ONHOLD,
        ]);
    }

    public function closedSubmissions()
    {
        return $this->hasMany(RecruitmentSubmission::class)->whereNotIn('status', [
            RecruitmentSubmissionStatus::PENDING,
            RecruitmentSubmissionStatus::INPROGRESS,
            RecruitmentSubmissionStatus::ONHOLD,
        ]);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function relatedRole()
    {
        return $this->belongsTo(Role::class, 'related_role_id');
    }
}
