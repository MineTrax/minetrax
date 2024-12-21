<?php

namespace App\Queries\Filters;

use App\Utils\Helpers\Helper;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class FilterMultipleFields implements Filter
{
    public function __construct(public $fields)
    {
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $fields = $this->fields;

        $query->where(function ($query) use ($fields, $value) {
            foreach ($fields as $field) {
                $val = Helper::escapeLike($value);
                // Handle Relation Field
                if ($this->isRelationProperty($query, $field)) {
                    $query->orWhereHas(Str::before($field, '.'), function ($query) use ($field, $val) {
                        $query->where(Str::after($field, '.'), 'LIKE', "%" . $val . "%");
                    });
                    continue;
                } else {
                    // Handle No Relation Field
                    $query->orWhere($field, 'LIKE', "%" . $val . "%");
                }
            }
        });

        return $query;
    }


    private function isRelationProperty(Builder $query, string $property): bool
    {
        if (!Str::contains($property, '.')) {
            return false;
        }

        $firstRelationship = explode('.', $property)[0];

        if (!method_exists($query->getModel(), $firstRelationship)) {
            return false;
        }

        return is_a($query->getModel()->{$firstRelationship}(), Relation::class);
    }
}
