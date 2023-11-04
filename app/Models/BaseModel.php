<?php

namespace App\Models;

use App\Traits\HasPermissionsTrait;
use App\Utils\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasPermissionsTrait;

    protected $guarded = [];

    public static function fastCount()
    {
        $tableName = (new static)->getTable();

        return Helper::fastCount($tableName);
    }
}
