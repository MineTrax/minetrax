<?php

namespace App\Models;

use App\Enums\CustomFormStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class CustomForm extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array',
        'status' => CustomFormStatus::class,
        'is_notify_staff_on_submission' => 'boolean',
        'is_visible_in_listing' => 'boolean',
    ];

    public function getDescriptionHtmlAttribute(): ?string
    {
        $converter = new GithubFlavoredMarkdownConverter();

        return $this->description ? $converter->convertToHtml($this->description) : null;
    }

    public function submissions()
    {
        return $this->hasMany(CustomFormSubmission::class);
    }
}
