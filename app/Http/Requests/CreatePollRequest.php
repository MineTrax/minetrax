<?php

namespace App\Http\Requests;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;

class CreatePollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Gate::allows('create', Poll::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required|string|max:255',
            'options' => 'required|array|max:10|min:2',
            'options.*.name' => 'required|string|max:50',
            'started_at' => 'sometimes|nullable|date',
            'closed_at' => 'sometimes|nullable|date|after:now'
        ];
    }
}
