<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Server;
use App\Settings\PluginSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $serversForRankSync = Server::select(['id', 'name', 'hostname'])
            ->where('type', '!=', ServerType::Bungee)
            ->get()->keyBy('id')->map(function ($value) {
                return '#'.$value->id.' - '.$value->name.' - '.$value->hostname;
            });

        $serversForAccountLink = Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get();
        $accountLinkAfterSuccessCommands = Command::select('id', 'command', 'is_run_on_all_servers', 'config')
            ->whereIn('id', $settings->account_link_after_success_commands)->with('servers:id,name,hostname')->get();

        return Inertia::render('Admin/Setting/PluginSetting', [
            'settings' => $settings->toArray(),
            'settingsAccountLinkAfterSuccessCommands' => $accountLinkAfterSuccessCommands,
            'serversForRankSync' => $serversForRankSync,
            'serversForAccountLink' => $serversForAccountLink,
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
            'enable_sync_player_ranks_from_server' => ['required', 'boolean'],
            'sync_player_ranks_from_server_id' => ['required_if:enable_sync_player_ranks_from_server,true', 'nullable', 'integer', 'exists:servers,id'],
            'account_link_after_success_commands' => ['nullable', 'array'],
            'account_link_after_success_commands.*.id' => ['nullable', 'integer', 'exists:commands,id'],
            'account_link_after_success_commands.*.command' => ['required', 'string'],
            'account_link_after_success_commands.*.servers' => ['nullable', 'array'],
            'account_link_after_success_commands.*.config' => ['required', 'array'],
            'account_link_after_success_commands.*.config.is_player_online_required' => ['required', 'boolean'],
            'account_link_after_success_commands.*.config.is_run_only_first_link' => ['required', 'boolean'],
        ]);

        $settings->enable_api = $request->enable_api;
        $settings->enable_account_link = $request->enable_account_link;
        $settings->max_players_link_per_account = $request->max_players_link_per_account;
        $settings->enable_sync_player_ranks_from_server = $request->enable_sync_player_ranks_from_server;
        $settings->sync_player_ranks_from_server_id = $request->sync_player_ranks_from_server_id;

        // Fetch all existing account link commands
        $existingAccountLinkCommands = $settings->account_link_after_success_commands;
        $updatedAccountLinkCommands = [];
        if ($request->account_link_after_success_commands) {
            foreach ($request->account_link_after_success_commands as $command) {
                $id = array_key_exists('id', $command) ? $command['id'] : null;
                $isRunOnAllServers = count($command['servers']) > 0 ? false : true;
                $serverIds = Arr::pluck($command['servers'], 'id');

                // If there isn't any id, then it's a new command. add to database.
                if (! $id) {
                    $created = Command::create([
                        'command' => $command['command'],
                        'name' => 'Account Link Command',
                        'tag' => 'account_link_command',
                        'description' => null,
                        'is_enabled' => true,
                        'is_run_on_all_servers' => $isRunOnAllServers,
                        'max_attempts' => 1,
                        'config' => $command['config'],
                    ]);
                    $created->servers()->sync($serverIds);
                    $updatedAccountLinkCommands[] = $created->id;
                }
                // If there is an id, then it's an existing command. update it.
                else {
                    $found = Command::where('id', $id)->first();
                    if (! $found) {
                        continue;
                    }
                    $found->update([
                        'command' => $command['command'],
                        'is_run_on_all_servers' => $isRunOnAllServers,
                        'config' => $command['config'],
                    ]);

                    $found->servers()->sync($serverIds);
                    $updatedAccountLinkCommands[] = $id;
                }
            }

            // Finally, find all IDs from existing commands that are not in the updated commands and delete them.
            $deletedCommands = array_diff($existingAccountLinkCommands, $updatedAccountLinkCommands);
            if (count($deletedCommands) > 0) {
                Command::whereIn('id', $deletedCommands)->delete();
            }

            $settings->account_link_after_success_commands = $updatedAccountLinkCommands;
        } else {
            $settings->account_link_after_success_commands = [];
        }

        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Plugin Settings Updated Successfully')]]);
    }
}
