<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomFormSubmission extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    public function customForm()
    {
        return $this->belongsTo(CustomForm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
