<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Gate::allows('create', Post::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $maxMediaSize = config('minetrax.max_post_feed_media_size_kb');

        return [
            'media' => 'max:4',
            'media.*' => [
                'sometimes',
                'nullable',
                'image',
                'max:'.$maxMediaSize,
            ],
            'body' => 'required_without:media|max:1000',
        ];
    }
}
