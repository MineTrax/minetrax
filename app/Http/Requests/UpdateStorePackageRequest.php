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

    /**
     * A blank slug keeps the one the package already has rather than rebuilding it from the name.
     *
     * The slug is the package's public URL, so re-deriving it on every save meant renaming a
     * package silently broke every link anybody had ever posted to it.
     */
    protected function resolvedSlug(): string
    {
        $submitted = Str::slug((string) $this->input('slug'));

        return $submitted !== '' ? $submitted : (string) $this->route('storePackage')->slug;
    }

    public function rules(): array
    {
        $id = $this->route('storePackage')->id;

        return array_merge($this->baseRules(), [
            'slug' => array_merge($this->slugRules(), [
                Rule::unique('store_packages', 'slug')->ignore($id),
            ]),
            // A package that requires itself could never be bought.
            'required_packages.*' => [
                'required', 'integer', 'distinct', 'exists:store_packages,id', Rule::notIn([$id]),
            ],
        ]);
    }
}
