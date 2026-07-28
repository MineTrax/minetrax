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
        $id = $this->route('storePackage')->id;

        return array_merge($this->baseRules(), [
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('store_packages', 'slug')->ignore($id),
            ],
            // A package that requires itself could never be bought.
            'required_packages.*' => [
                'required', 'integer', 'distinct', 'exists:store_packages,id', Rule::notIn([$id]),
            ],
        ]);
    }
}
