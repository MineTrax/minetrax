<?php

namespace App\Models;

use App\Enums\CustomFormStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomForm extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array',
        'status' => CustomFormStatus::class,
        'require_restricted_permission_to_view_submission' => 'boolean',
        'is_notify_staff_on_submission' => 'boolean',
    ];
}
