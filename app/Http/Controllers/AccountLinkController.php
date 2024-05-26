<?php

namespace App\Http\Controllers;

use App\Jobs\AccountUnlinkAfterSuccessCommandJob;
use App\Models\Player;
use App\Settings\PluginSettings;
use App\Utils\Helpers\AccountLinkUtils;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountLinkController extends Controller
{
    public function listMyPlayers(Request $request, PluginSettings $pluginSettings)
    {
        $linkedPlayers = $request->user()->players()->with(['country', 'rank:id,name,shortname'])->get();

        $currentLinkOtp = AccountLinkUtils::generateOtp($request->user()->id);

        return Inertia::render('User/ListLinkedPlayer', [
            'linkedPlayers' => $linkedPlayers,
            'maxPlayerPerUser' => $pluginSettings->max_players_link_per_account,
            'currentLinkOtp' => $currentLinkOtp,
        ]);
    }

    public function unlink(Player $player, Request $request)
    {
        $unlinkingDisabled = config('minetrax.disable_player_unlinking');
        if ($unlinkingDisabled) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('Player unlinking disabled!'), 'body' => __('Player unlinking is disabled by the administrator.'), 'milliseconds' => 10000]]);
        }

        // Make sure the player is linked to current user who is running to unlink
        $user = $request->user();
        $userHasPlayer = $user->players()->where('player_id', $player->id)->exists();
        if (! $userHasPlayer) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('Player not found!'), 'body' => __('No player with that ID found linked to your account.'), 'milliseconds' => 10000]]);
        }

        // Unlink the player
        $user->players()->detach($player->id);

        // If MARK_USER_VERIFYED_ON_ACCOUNT_LINK is enabled then mark user as unverified when he unlink all players.
        $linkedPlayersExist = $user->players()->exists();
        if (config('minetrax.mark_user_verified_on_account_link') && $user->verified_at && ! $linkedPlayersExist) {
            $user->verified_at = null;
            $user->save();
        }

        // Dispatch unlink after success commands
        AccountUnlinkAfterSuccessCommandJob::dispatch($player, $user->id);

        // Return redirect back with flash
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Played unlinked successfully!'), 'body' => __('Player has been removed from your account.'), 'milliseconds' => 10000]]);
    }
}
