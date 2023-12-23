<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomForm extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array',
    ];
}
