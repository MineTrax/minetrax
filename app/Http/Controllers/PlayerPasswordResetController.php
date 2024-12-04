<?php

namespace App\Http\Controllers;

use App\Jobs\PlayerPasswordResetCommandJob;
use App\Models\Player;
use App\Settings\PluginSettings;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PlayerPasswordResetController extends Controller
{
    public function show(Request $request, PluginSettings $pluginSettings)
    {
        $featureEnabled = $pluginSettings->enable_player_password_reset;
        if (!$featureEnabled) {
            return redirect()->route('home')
                ->with(['toast' => ['type' => 'danger', 'title' => __('Feature disabled!'), 'body' => __('This feature is disabled by administrator.'), 'milliseconds' => 10000]]);
        }

        $linkedPlayers = $request->user()->players()->select('uuid', 'players.id', 'username')->get();
        $selectedPlayerUuid = $request->query('player_uuid');

        // Cooldown Check.
        $cooldown = Cache::get('player_password_reset::user::' . $request->user()->id);
        if ($cooldown) {
            $cooldown = now()->diffInSeconds($cooldown->addSeconds($pluginSettings->player_password_reset_cooldown_in_seconds), false);
        }

        return Inertia::render('Player/ResetPassword', [
            'uuid' => $selectedPlayerUuid,
            'players' => $linkedPlayers,
            'cooldown' => $cooldown,
            'cannotPlayerPasswordReset' => $request->user()->hasPermissionTo('cannot player_password_reset'),
        ]);
    }

    public function update(Request $request, PluginSettings $pluginSettings)
    {
        $featureEnabled = $pluginSettings->enable_player_password_reset;
        $cooldownInSeconds = $pluginSettings->player_password_reset_cooldown_in_seconds;
        if (!$featureEnabled) {
            return redirect()->route('home')
                ->with(['toast' => ['type' => 'danger', 'title' => __('Feature disabled!'), 'body' => __('This feature is disabled by administrator.'), 'milliseconds' => 10000]]);
        }

        $request->validate([
            'player_uuid' => 'required|uuid|exists:players,uuid',
            'new_password' => ['required', 'string', Password::min(8)->uncompromised()],
            'reason' => 'nullable|string|max:255',
        ]);

        // Permission check.
        $player = Player::where('uuid', $request->player_uuid)->firstOrFail();
        $this->authorize('resetPassword', $player);

        // Cooldown Handling.
        $cooldown = Cache::get('player_password_reset::user::' . $request->user()->id);
        if ($cooldown) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('Cooldown!'), 'body' => __('You can reset password once every :seconds seconds.', ['seconds' => $cooldownInSeconds]), 'milliseconds' => 10000]]);
        }
        Cache::put('player_password_reset::user::' . $request->user()->id, now(), $cooldownInSeconds);

        // Fire Job to reset password.
        PlayerPasswordResetCommandJob::dispatchSync(
            $player,
            $request->user()->id,
            $request->new_password,
            $request->reason
        );

        // redirect back.
        return redirect()->back()
            ->with([
                'success' => true,
                'toast' => [
                    'type' => 'success',
                    'title' => __('Action Successful!'),
                ]
            ]);
    }
}
