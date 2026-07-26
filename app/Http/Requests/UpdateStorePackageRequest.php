<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateStorePackageRequest extends CreateStorePackageRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storePackage'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('store_packages', 'slug')->ignore($this->route('storePackage')->id),
            ],
        ]);
    }
}
