<?php

namespace App\Http\Requests;

use App\Models\StoreTax;

class UpdateStoreTaxRequest extends CreateStoreTaxRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('storeTax') ?? StoreTax::class);
    }
}
