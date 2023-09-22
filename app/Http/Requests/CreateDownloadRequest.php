<?php

namespace App\Http\Requests;

use App\Models\Download;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Download::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:downloads',
            'description' => 'nullable|string',
            'is_external' => 'required|boolean',
            'file_url' => 'nullable|required_if:is_external,true|url',
            'file_name' => 'nullable|required_if:is_external,true|string|max:255',
            'is_external_url_hidden' => 'nullable|required_if:is_external,true|boolean',
            'is_only_auth' => 'required|boolean',
            'min_role_weight_required' => 'nullable|integer',
            'is_active' => 'required|boolean',

            'file' => 'nullable|required_if:is_external,false|file|max:102400', //100mb
        ];
    }
}
