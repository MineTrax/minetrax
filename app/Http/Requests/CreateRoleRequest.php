<?php

namespace App\Http\Requests;

use App\Models\Role;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Role::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|alpha_dash|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'is_staff' => 'required|boolean',
            'is_hidden_from_staff_list' => 'required|boolean',
            'photo' => 'required|image|max:300',
            'weight' => 'required|integer',
            'web_message_format' => 'nullable|string|max:255',
            'permissions' => 'sometimes|nullable|array|exists:permissions,name'
        ];
    }
}
