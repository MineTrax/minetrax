<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateStoreCategoryRequest extends CreateStoreCategoryRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeCategory'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        $category = $this->route('storeCategory');

        return array_merge($this->baseRules(), [
            'slug' => ['required', 'string', 'max:255', Rule::unique('store_categories', 'slug')->ignore($category->id)],
            // A category cannot be its own parent, which would orphan the subtree.
            'parent_id' => ['nullable', 'integer', 'exists:store_categories,id', Rule::notIn([$category->id])],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'parent_id.not_in' => __('A category cannot be its own parent.'),
        ]);
    }
}
