<?php

namespace App\Traits;

use App\Models\Command;

trait HasCommandsTrait
{
    public function commands()
    {
        return $this->morphToMany(Command::class, 'commandable');
    }
}
