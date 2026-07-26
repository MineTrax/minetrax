<?php

namespace App\Http\Requests;

use App\Models\StoreCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateStoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreCategory::class);
    }

    /**
     * The slug is derived from the name rather than accepted from the client, so uniqueness can
     * be validated against the value that will actually be stored.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:store_categories,slug',
            'description' => 'nullable|string|max:5000',
            'parent_id' => 'nullable|integer|exists:store_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'required|boolean',
            'is_enabled' => 'required|boolean',
            'photo' => 'nullable|image|max:5120',
        ];
    }
}
