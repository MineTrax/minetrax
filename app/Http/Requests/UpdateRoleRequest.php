<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'display_name' => 'required|string|max:255',
            'is_staff' => 'required|boolean',
            'is_hidden_from_staff_list' => 'required|boolean',
            'weight' => 'required|integer',
            'web_message_format' => 'nullable|string|max:255',
            'photo' => 'sometimes|nullable|image|max:300',
            'permissions' => 'sometimes|nullable|array|exists:permissions,name'
        ];
    }
}
