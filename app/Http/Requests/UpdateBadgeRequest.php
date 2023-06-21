<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBadgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'shortname' => [
                'required',
                'alpha_dash',
                'max:25',
                Rule::unique('badges')->ignore($this->route('badge')),
            ],
            'sort_order' => 'sometimes|nullable|numeric|min:0',
            'is_sticky' => 'required|boolean',
            'photo' => 'sometimes|nullable|image|max:200'
        ];
    }
}
