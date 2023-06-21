<?php

namespace App\Http\Requests;

use App\Models\News;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateNewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', News::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'body' => 'required',
            'is_published' => 'required|boolean',
            'is_pinned' => 'required|boolean',
            'type' => 'required|numeric|in:0,1,2',   // @TODO change to NewsType
            'photo' => 'sometimes|nullable|image|max:2048'
        ];
    }
}
