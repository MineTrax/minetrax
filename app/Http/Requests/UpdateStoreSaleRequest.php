<?php

namespace App\Http\Requests;

use Gate;

class UpdateStoreSaleRequest extends CreateStoreSaleRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeSale'));
    }
}
