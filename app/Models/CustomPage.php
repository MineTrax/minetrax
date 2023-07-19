<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class CustomPage extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'is_visible' => 'boolean',
        'is_in_navbar' => 'boolean',
        'is_redirect' => 'boolean',
        'is_sidebar_visible' => 'boolean',
        'is_open_in_new_tab' => 'boolean',
        'is_html_page' => 'boolean',
    ];

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeNavbar($query)
    {
        return $query->where('is_in_navbar', true);
    }

    public function getBodyHtmlAttribute(): string
    {
        if ($this->is_html_page) {
            return $this->body;
        }
        $converter = new GithubFlavoredMarkdownConverter();
        return $converter->convertToHtml($this->body);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
