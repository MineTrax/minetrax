<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Poll extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
        'started_at' => 'datetime',
    ];

    protected $with  = ['options'];

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votes(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(PollVote::class, PollOption::class);
    }

    /**
     * Check if a Poll is closed, either manually or by passed deadline
     *
     * @return bool
     */
    public function isLockedOrClosed(): bool
    {
        return $this->is_closed || $this->isPollClosed();
    }

    public function isPollClosed(): bool
    {
        if (!$this->closed_at) return false;

        return now() > $this->closed_at;
    }

    public function isVotable(): bool
    {
        return !$this->isLockedOrClosed() && now() >= $this->started_at;
    }

    public function isComingSoon(): bool
    {
        return $this->started_at >= now();
    }

    /**
     * Check if the user can change options
     * User can change if there is no votes to the poll yet.
     * @TODO: Remove as its not needed
     * @return bool
     */
    public function canUpdateThisPoll(): bool
    {
        return $this->votes()->count() === 0;
    }

    public function getParsedForVueComponent($user = null)
    {
        $voted_option_id = null;
        if ($user) {
            $voted_option_id = $user->pollOptions()->where('poll_id', $this->id)->first()?->id;
        }
        return [
            'id' => $this->id,
            'question' => $this->question,
            'started_at' => $this->started_at,
            'closed_at' => $this->closed_at,
            'answers' => $this->options->map(function($option) use($voted_option_id) {
                return [
                    'value' => $option->id,
                    'text' => $option->name,
                    'votes' => $option->votes,
                    'selected' => $option->id == $voted_option_id
                ];
            }),
            'finalResults' => $this->isLockedOrClosed(),
            'isComingSoon' => $this->isComingSoon(),  // This is true if poll has not started yet
            'showResults' => !!$voted_option_id, // This is true if User has vote already
        ];
    }

    // in PollController index: Used for appending json_for_vue to all polls via a loop
    public function getJsonForVueAttribute()
    {
        return $this->getParsedForVueComponent(request()->user());
    }
}
