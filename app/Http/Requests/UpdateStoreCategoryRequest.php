<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateStoreCategoryRequest extends FormRequest
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

        return [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('store_categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string|max:5000',
            // A category cannot be its own parent, which would orphan the subtree.
            'parent_id' => ['nullable', 'integer', 'exists:store_categories,id', Rule::notIn([$category->id])],
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'required|boolean',
            'is_enabled' => 'required|boolean',
            'photo' => 'nullable|image|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => __('A category cannot be its own parent.'),
        ];
    }
}
