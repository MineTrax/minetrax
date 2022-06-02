<?php

namespace App\Models;

use App\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasPermissionsTrait;

    protected $guarded = [];
}
