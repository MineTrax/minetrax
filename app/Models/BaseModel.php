<?php

namespace App\Models;

use App\Enums\Concerns\HasKeyValueSerialization;
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

    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getCasts() as $key => $castType) {
            if (
                isset($attributes[$key]) &&
                is_string($castType) &&
                enum_exists($castType) &&
                is_subclass_of($castType, \BackedEnum::class) &&
                is_subclass_of($castType, HasKeyValueSerialization::class)
            ) {
                $enum = $castType::tryFrom($attributes[$key]);
                if ($enum !== null) {
                    $attributes[$key] = [
                        'key' => $enum->name,
                        'value' => $enum->value,
                    ];
                }
            }
        }

        return $attributes;
    }
}
