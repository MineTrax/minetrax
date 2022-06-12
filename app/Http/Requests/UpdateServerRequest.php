<?php

namespace App\Http\Requests;

use App\Enums\ServerVersion;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServerRequest extends FormRequest
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
            'connection_type' => 'required|in:ftp,sftp,local',
            'storage_server_host' => 'required_if:connection_type,ftp,sftp',
            'storage_server_port' => 'nullable|numeric|min:0|max:65535',
            'storage_server_username' => 'nullable|required_if:connection_type,ftp,sftp|string',
            'storage_server_password' => 'required_if:connection_type,ftp,sftp',
            'storage_server_root' => 'sometimes|required_if:connection_type,local|nullable',
            'storage_server_key' => 'sometimes|nullable',
            'hostname' => 'required',
            'join_port' => 'required|numeric|min:0|max:65535',
            'query_port' => 'required|numeric|min:0|max:65535',
            'webquery_port' => 'required|numeric|min:0|max:65535',
            'name' => 'required',
            'minecraft_version' => ['required', new EnumValue(ServerVersion::class)],
            'type' => 'required',
            'level_name' => 'required|alpha_dash',
            'settings' => 'sometimes',
            'is_stats_tracking_enabled' => 'required|boolean',
            'is_ingame_chat_enabled' => 'required|boolean',
            'is_online_players_query_enabled' => 'required|boolean',
        ];
    }
}
