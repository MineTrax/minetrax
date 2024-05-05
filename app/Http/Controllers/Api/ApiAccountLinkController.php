<?php

namespace App\Http\Controllers\Api;

use App\Jobs\AccountLinkAfterSuccessCommandJob;
use App\Models\Player;
use App\Models\Server;
use App\Models\User;
use App\Settings\PluginSettings;
use App\Utils\Helpers\AccountLinkUtils;
use Illuminate\Http\Request;

class ApiAccountLinkController extends ApiController
{
    public function verify(Request $request, PluginSettings $pluginSettings)
    {
        $request->validate([
            'data' => 'required|array',
            'data.uuid' => 'required|uuid',
            'data.otp' => 'required|string|size:6',
            'data.server_id' => 'required|exists:servers,id',
        ]);

        // Find player with UUID if not found then tell player to wait
        $player = Player::where('uuid', $request->input('data.uuid'))->first();
        if (! $player) {
            return $this->error(__('Player not found'), 'not-found', 404);
        }

        // Check if player is already linked to some user
        if ($player->users()->count() > 0) {
            return $this->error(__('Player already linked to a user'), 'already-linked');
        }

        // Check if OTP is valid
        $valid = AccountLinkUtils::verifyOtp($request->input('data.otp'));
        if (! $valid) {
            return $this->error(__('Provided OTP is invalid or expired. Please try again.'), 'otp-invalid');
        }

        // Check if current user has enough available slot to link the player.
        $user = User::where('id', $valid['user_id'])->first();
        $max_slots = $pluginSettings->max_players_link_per_account; // Total number of players that can be linked to account
        if ($user->players()->count() >= $max_slots) {
            $this->error(__('You already have max :max_slots players linked!', ['max_slots' => $max_slots]), 'max-players-reached');
        }

        // Link player to user
        $user->players()->attach($player);
        AccountLinkUtils::forgetOtp($request->input('data.otp'));

        // If MARK_USER_VERIFYED_ON_ACCOUNT_LINK is enabled then mark user as verified.
        if (config('minetrax.mark_user_verified_on_account_link') && $user->verified_at == null) {
            $user->verified_at = now();
            $user->save();
        }

        // Run command to give this player the reward according to Plugin setting if enabled
        $server = Server::find($request->input('data.server_id'));
        AccountLinkAfterSuccessCommandJob::dispatch($player, $user->id, $server);

        return $this->success(null, __('Player linked successfully'));
    }
}
