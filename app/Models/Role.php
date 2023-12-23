<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Role extends \Spatie\Permission\Models\Role implements HasMedia
{
    use InteractsWithMedia;

    protected $appends = ['photo_url'];

    protected $with = ['media'];

    protected $casts = [
        'is_staff' => 'boolean',
        'is_hidden_from_staff_list' => 'boolean',
    ];

    const DEFAULT_ROLE_NAME = 'default';

    const SUPER_ADMIN_ROLE_NAME = 'superadmin';

    // For spatie/laravel-permissions https://github.com/spatie/laravel-permission/issues/1540
    protected $guard_name = 'sanctum';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('role')
            ->singleFile();
    }

    public function getPhotoUrlAttribute()
    {
        $photo_url = url('images/default-roles/'.$this->name.'.png');
        if ($this->getFirstMediaUrl('role')) {
            $photo_url = $this->getFirstMediaUrl('role');
        }

        return $photo_url;
    }
}
