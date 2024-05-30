<?php

namespace App\Http\Requests;

use App\Models\CommandQueue;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateCommandQueueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', CommandQueue::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scope' => 'required|string|in:global,player',
            'command' => 'required|string',
            'execute_at' => 'nullable|date|after:now',
            'servers' => 'nullable|array',
            'servers.*.id' => 'required|int|exists:servers,id',
            'players' => 'required_if:scope,player|array',
            'players.scope' => 'required_if:scope,player|in:all,linked,unlinked,custom',
            'players.is_player_online_required' => 'required_if:scope,player|boolean',
            'players.id' => 'required_if:players.scope,custom|array',
        ];
    }
}
