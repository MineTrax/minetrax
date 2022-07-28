<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Settings\PluginSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PluginSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(PluginSettings $settings)
    {
        $serverList = Server::select(['id', 'name', 'hostname'])
            ->where('type', '!=', ServerType::Bungee)
            ->get()->keyBy('id')->map(function($value) {
                return '#'.$value->id.' - '.$value->name.' - '.$value->hostname;
            });

        return Inertia::render('Admin/Setting/PluginSetting',[
            'settings' => $settings->toArray(),
            'servers' => $serverList
        ]);
    }

    public function regeneratePluginApiKeys(PluginSettings $settings): \Illuminate\Http\RedirectResponse
    {
        $settings->plugin_api_key = Str::random(32);
        $settings->plugin_api_secret = Str::random(32);
        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Keys Regenerated Successfully')]]);
    }

    public function update(Request $request, PluginSettings $settings): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'enable_api' => ['required', 'boolean'],
            'enable_account_link' => ['required', 'boolean'],
            'max_players_link_per_account' => ['required', 'integer', 'min:1', 'max:999'],
            'account_link_after_success_command' => ['nullable', 'string', 'max:255'],
            'account_link_after_success_broadcast_message' => ['nullable', 'string', 'max:1000'],
            'enable_sync_player_ranks_from_server' => ['required', 'boolean'],
            'sync_player_ranks_from_server_id' => ['required_if:enable_sync_player_ranks_from_server,true', 'nullable', 'integer', 'exists:servers,id']
        ]);

        $settings->enable_api = $request->enable_api;
        $settings->enable_account_link = $request->enable_account_link;
        $settings->max_players_link_per_account = $request->max_players_link_per_account;
        $settings->account_link_after_success_command = $request->account_link_after_success_command;
        $settings->account_link_after_success_broadcast_message = $request->account_link_after_success_broadcast_message;

        $settings->enable_sync_player_ranks_from_server = $request->enable_sync_player_ranks_from_server;
        $settings->sync_player_ranks_from_server_id = $request->sync_player_ranks_from_server_id;

        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Plugin Settings Updated Successfully')]]);
    }
}
