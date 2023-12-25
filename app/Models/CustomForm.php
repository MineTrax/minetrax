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
        'require_restricted_permission_to_view_submission' => 'boolean',
        'is_notify_staff_on_submission' => 'boolean',
    ];

    public function getDescriptionHtmlAttribute(): string|null
    {
        $converter = new GithubFlavoredMarkdownConverter();

        return $this->description ? $converter->convertToHtml($this->description) : null;
    }
}
