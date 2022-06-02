<?php

namespace App\Models;

use App\Traits\HasCommentsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends BaseModel
{
    use HasFactory, HasCommentsTrait;

    protected $casts = [
        'is_approved' => 'boolean'
    ];

    // Appends permission to each request
    public function toArray(): array
    {
        return parent::toArray() + [
                'permissions' => $this->permissions(['delete'])
            ];
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function commentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function commentator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo($this->getAuthModelName(), 'user_id');
    }

    public function approve()
    {
        $this->update([
            'is_approved' => true,
        ]);

        return $this;
    }

    public function disapprove()
    {
        $this->update([
            'is_approved' => false,
        ]);

        return $this;
    }

    protected function getAuthModelName()
    {

        if (!is_null(config('auth.providers.users.model'))) {
            return config('auth.providers.users.model');
        }
        return User::class;
    }
}
