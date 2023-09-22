<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('downloads')->ignore($this->route('download')),
            ],
            'description' => 'nullable|string',
            'file_name' => 'nullable|required_if:is_external,true|string|max:255',
            'is_external_url_hidden' => 'nullable|required_if:is_external,true|boolean',
            'is_only_auth' => 'required|boolean',
            'min_role_weight_required' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ];
    }
}
