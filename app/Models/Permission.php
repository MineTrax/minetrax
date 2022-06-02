<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends \Spatie\Permission\Models\Permission
{
    // For spatie/laravel-permissions https://github.com/spatie/laravel-permission/issues/1540
    public $guard_name = 'sanctum';
}
