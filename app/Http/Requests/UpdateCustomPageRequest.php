<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomPageRequest extends FormRequest
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
            'title' => 'required|max:255|string',
            'path' => [
                'required',
                'alpha_dash',
                'max:100',
                Rule::unique('custom_pages')->ignore($this->route('customPage')),
            ],
            'is_visible' => 'required|boolean',
            'is_sidebar_visible' => 'required|boolean',
            'is_in_navbar' => 'required|boolean',
            'is_redirect' => 'required|boolean',
            'is_html_page' => 'required|boolean',
            'is_open_in_new_tab' => 'required|boolean',
            'redirect_url' => 'nullable|required_if:is_redirect,true|url',
            'body' => 'nullable|required_if:is_redirect,false|string',
        ];
    }
}
