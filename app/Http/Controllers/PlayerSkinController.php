<?php

namespace App\Http\Controllers;

use App\Enums\ServerType;
use App\Jobs\ChangePlayerSkinJob;
use App\Models\Player;
use App\Models\Server;
use App\Utils\Helpers\MinecraftSkinUtils;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PlayerSkinController extends Controller
{
    public function showChangeSkin(Request $request)
    {
        $featureEnabled = config('minetrax.player_skin_changer_enabled');
        if (! $featureEnabled) {
            return redirect()->route('home')
                ->with(['toast' => ['type' => 'danger', 'title' => __('Feature disabled!'), 'body' => __('This feature is disabled by administrator.'), 'milliseconds' => 10000]]);
        }

        $linkedPlayers = $request->user()->players()->select('uuid', 'players.id', 'username')->get();
        $selectedPlayerUuid = $request->query('player_uuid');

        // has servers which supports skin change feature.
        $hasServersWithFeature = Server::where('type', '!=', ServerType::Bungee)
            ->select('settings')->get()->some(function ($server) {
                return $server->getSettingsKey('is_skin_change_via_web_allowed');
            });

        // Cooldown Check.
        $isUserStaff = $request->user()->isStaffMember() || $request->user()->can('change any_player_skin');
        $cooldown = Cache::get('player_skin_changer_cooldown::user::'.$request->user()->id);
        if ($cooldown) {
            $cooldown = now()->diffInSeconds($cooldown->addSeconds(config('minetrax.player_skin_changer_cooldown_in_seconds')), false);
        }

        // Special. Let Admin to change any player's skin.
        $playerInLinked = $linkedPlayers->firstWhere('uuid', $selectedPlayerUuid);
        if (! $playerInLinked && $selectedPlayerUuid && $request->user()->can('change any_player_skin')) {
            $linkedPlayers = Player::select('uuid', 'id', 'username')->get();
        }

        return Inertia::render('Player/ChangeSkin', [
            'uuid' => $selectedPlayerUuid,
            'players' => $linkedPlayers,
            'hasServersWithFeature' => $hasServersWithFeature,
            'cooldown' => $isUserStaff ? null : $cooldown,
        ]);
    }

    public function postChangeSkin(Request $request)
    {
        $featureEnabled = config('minetrax.player_skin_changer_enabled');
        $cooldownInSeconds = config('minetrax.player_skin_changer_cooldown_in_seconds');
        if (! $featureEnabled) {
            return redirect()->route('home')
                ->with(['toast' => ['type' => 'danger', 'title' => __('Feature disabled!'), 'body' => __('This feature is disabled by administrator.'), 'milliseconds' => 10000]]);
        }

        $request->validate([
            'player_uuid' => 'required|uuid|exists:players,uuid',
            'action_type' => 'required|in:upload,username,url,reset',
            'file' => 'nullable|required_if:action_type,upload|file|mimes:png',
            'skin_type' => 'nullable|required_if:action_type,upload|in:steve,alex',
            'url' => 'nullable|required_if:action_type,url|url',
            'username' => 'nullable|required_if:action_type,username|string',
        ]);

        // Permission check.
        $player = Player::where('uuid', $request->player_uuid)->firstOrFail();
        $this->authorize('changeSkin', $player);

        // Cooldown Handling.
        $cooldown = Cache::get('player_skin_changer_cooldown::user::'.$request->user()->id);
        $isUserStaff = $request->user()->isStaffMember() || $request->user()->can('change any_player_skin');
        if ($cooldown && ! $isUserStaff) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('Cooldown!'), 'body' => __('You can change skin once every :seconds seconds.', ['seconds' => $cooldownInSeconds]), 'milliseconds' => 10000]]);
        }
        Cache::put('player_skin_changer_cooldown::user::'.$request->user()->id, now(), $cooldownInSeconds);

        // get servers which supports skin change feature.
        $servers = Server::where('type', '!=', ServerType::Bungee)->get()->filter(function ($server) {
            return $server->getSettingsKey('is_skin_change_via_web_allowed');
        });

        switch ($request->action_type) {
            case 'upload':
                try {
                    $response = MinecraftSkinUtils::uploadSkinToMineSkin($request->file, $request->skin_type);
                    $value = $response['data']['texture']['value'];
                    $signature = $response['data']['texture']['signature'];
                    $payload = $value.':::'.$signature;
                    foreach ($servers as $server) {
                        ChangePlayerSkinJob::dispatch($server, $request->player_uuid, 'upload', $payload);
                    }
                } catch (\Exception $e) {
                    throw ValidationException::withMessages(['file' => $e->getMessage()]);
                }
                break;
            case 'username':
                foreach ($servers as $server) {
                    ChangePlayerSkinJob::dispatch($server, $request->player_uuid, 'username', $request->username);
                }
                break;
            case 'url':
                foreach ($servers as $server) {
                    ChangePlayerSkinJob::dispatch($server, $request->player_uuid, 'url', $request->url);
                }
                break;
            case 'reset':
                // queue for all servers
                foreach ($servers as $server) {
                    ChangePlayerSkinJob::dispatch($server, $request->player_uuid, 'reset', null);
                }
                break;
        }

        // redirect back.
        return redirect()->back()
            ->with([
                'toast' => [
                    'type' => 'success',
                    'title' => __('Action Successful!'),
                    'body' => __('Skin change request has been queued. It will take some time to apply on all servers.'),
                    'milliseconds' => 15000,
                ]]);
    }
}
