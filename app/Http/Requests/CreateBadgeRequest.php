<?php

namespace App\Http\Requests;

use App\Models\Badge;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateBadgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Badge::class);
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
            'shortname' => 'required|alpha_dash|max:25|unique:badges',
            'sort_order' => 'sometimes|nullable|numeric|min:0',
            'is_sticky' => 'required|boolean',
            'photo' => 'required|image|max:200'
        ];
    }
}
