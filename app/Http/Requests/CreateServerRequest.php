<?php

namespace App\Http\Requests;

use App\Enums\ServerVersion;
use App\Models\Server;
use App\Rules\IpOrFqdn;
use BenSampo\Enum\Rules\EnumValue;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateServerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Server::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'hostname' => 'required|string',
            'ip_address' => ['required', new IpOrFqdn],
            'join_port' => 'required|numeric|min:0|max:65535',
            'query_port' => 'required|numeric|min:0|max:65535',
            'webquery_port' => [
                'nullable',
                'numeric',
                'min:0',
                'max:65535',
                Rule::requiredIf(function () {
                    return $this->is_server_intel_enabled || $this->is_player_intel_enabled || $this->is_ingame_chat_enabled;
                }),
                'different:join_port',
            ],
            'name' => 'required',
            'minecraft_version' => ['required', new EnumValue(ServerVersion::class)],
            'type' => 'required',
            'settings' => 'sometimes',
            'is_server_intel_enabled' => 'required|boolean',
            'is_player_intel_enabled' => 'required|boolean',
            'is_ingame_chat_enabled' => 'required|boolean',
            'order' => 'nullable|numeric',
            'settings.server_identifier' => 'nullable|alpha_dash',
            'settings.is_skin_change_via_web_allowed' => 'required|boolean',
            'settings.is_banwarden_enabled' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'webquery_port.required' => __('WebQuery is required for Player Intel, Server Intel or Ingame Chat.'),
        ];
    }
}
