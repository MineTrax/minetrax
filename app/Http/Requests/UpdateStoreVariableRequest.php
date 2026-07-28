<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;

class UpdateStoreVariableRequest extends CreateStoreVariableRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeVariable'));
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'identifier' => array_merge($this->identifierRules(), [
                Rule::unique('store_variables', 'identifier')->ignore($this->route('storeVariable')->id),
            ]),
        ]);
    }
}
