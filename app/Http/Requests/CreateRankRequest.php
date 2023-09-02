<?php

namespace App\Http\Requests;

use App\Models\Rank;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateRankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Rank::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'shortname' => 'required|alpha_dash|max:25|unique:ranks',
            'total_score_needed' => 'required|numeric|min:0',
            'total_play_time_needed' => 'required|numeric|min:0',
            'description' => 'nullable|max:1000',
            'photo' => 'required|image|max:512'
        ];
    }
}
