<?php

namespace App\Traits;

trait HasPermissionsTrait {
    public function permissions($abilities = []) {
        return collect(array_flip($abilities))->map(function($index, $ability) {
            return \Gate::allows($ability, $this);
        });
    }
}
