<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AccountUnlinkAfterSuccessCommandJob;
use App\Models\MinecraftPlayerSession;
use App\Models\Player;
use DB;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function destroy(Player $player)
    {
        $this->authorize('destroy', $player);

        DB::transaction(function () use ($player) {
            MinecraftPlayerSession::where('player_uuid', $player->uuid)->delete();
            $player->minecraftPlayers()->delete();
            $player->delete();
        });

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Player Deleted!'), 'body' => __('All intel data related to the player is deleted.')]]);
    }

    public function unlink(Player $player, Request $request)
    {
        $this->authorize('unlink', $player);

        $attachedUser = $player->users()->first();
        if (! $attachedUser) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('Not Linked!'), 'body' => __('Player is not linked to any user.')]]);
        }

        // Unlink the player
        $player->users()->detach($attachedUser->id);

        // If MARK_USER_VERIFYED_ON_ACCOUNT_LINK is enabled then mark user as unverified when he unlink all players.
        $linkedPlayersExist = $attachedUser->players()->exists();
        if (config('minetrax.mark_user_verified_on_account_link') && $attachedUser->verified_at && ! $linkedPlayersExist) {
            $attachedUser->verified_at = null;
            $attachedUser->save();
        }

        // Dispatch unlink after success commands
        AccountUnlinkAfterSuccessCommandJob::dispatch($player, $attachedUser->id);

        // Return redirect back with flash
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Played unlinked successfully!'), 'body' => __('Player has been unlinked from that account.'), 'milliseconds' => 10000]]);
    }
}
